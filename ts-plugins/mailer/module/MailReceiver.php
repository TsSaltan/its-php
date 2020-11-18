<?php
namespace tsframe\module;

use tsframe\exception\BaseException;

/**
 * Класс для получения почты по IMAP
 * @see IMAP commands https://k9mail.app/documentation/development/imapExtensions.html
 */
class MailReceiver {

    public static $IMAPServers = [
        'gmail.com' => 'imap.gmail.com',
        'googlemail.com' => 'imap.gmail.com',
        'onet.pl' => 'imap.poczta.onet.pl',
        'mail.ru' => 'map.mail.ru',
        'inbox.ru' => 'map.mail.ru',
        'list.ru' => 'map.mail.ru',
        'bk.ru' => 'map.mail.ru',
        'ya.ru' => 'imap.yandex.ru',
        'yandex.ru' => 'imap.yandex.ru',
        'yandex.by' => 'imap.yandex.ru',
        'yandex.ua' => 'imap.yandex.ru',
        'yandex.com' => 'imap.yandex.ru',
        'yandex.kz' => 'imap.yandex.ru',
    ];

    /**
     * Автоматическое определение IMAP сервера по домену почтового ящика
     */
    public static function detectIMAPServer(string $mail): ?string {
        $exp = explode('@', $mail);
        $domain = end($exp);

        return self::$IMAPServers[$domain] ?? null;
    }

    /**
     * Метод, через который будет выполнено соединение к IMAP серверу
     * @var string imap|curl
     */
    protected $method = 'imap';

    /**
     * Параметры для curl, испольуемые по умолчанию
     */
    protected $chParams = [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true
    ];

    /**
     * Параметры соединения
     */
	protected $connection = false, 
              $login, 
              $password, 
              $imapServer, 
              $imapPort;

    /**
     * Параметры запроса
     */
    protected $mailbox = "INBOX",
              $filter;

	public function __construct(string $login, string $password, string $imapServer, int $imapPort = 993){
        $this->login = $login;
        $this->password = $password;
        $this->imapServer = $imapServer;
        $this->imapPort = $imapPort;
	}

    /**
     * Установить тип соединения с IMAP сервером
     * @param string $method imap|curl
     */
    public function setConnectionMethod(string $method){
        $method = strtolower($method);
        if($method == 'imap' || $method == 'curl'){
            $this->method = $method;
        }
    }

    /**
     * Установить дополнительные параметры соединения (только для метода curl)
     * @param array $params массив с curl параметрами ключ => значение
     */
    public function setCurlParams(array $params){
        $this->chParams = array_merge($this->chParams, $params);
    }

    public function setMailbox(string $mailbox){
        $this->mailbox = strtoupper($mailbox);
    }

    /**
     * Поиск сообщений по фильтру
     * @return array id сообщений в почтовом ящике 
     * @see IMAP search commands http://www.marshallsoft.com/ImapSearch.htm
     */
    public function search(string $filter = "ALL") {
        $this->filter = strtoupper($filter);
        $mails = [];

        if($this->method == "imap"){
            $this->createImapConnection();
            $mails = imap_search($this->connection, $filter);
        }
        elseif($this->method == "curl"){
            $this->createCurlConnection();
            $this->setCurlURL('?' . urlencode($filter));
            $response = trim($this->getCurlResponse());
            if(stripos($response, 'SEARCH ') !== false){
                $exp = explode('SEARCH ', $response);
                $mails = explode(' ', $exp[1]);
                $mails = array_map(function($e){ return intval($e); }, $mails);
            } 
        }

        return is_array($mails) ? $mails : [];
    }

    /**
     * Получение сообщений с сервера по их id
     * @param array $ids идентификаторы сообщений
     * @return array [boundary => ?string, headers => [...], message => string, ts => int]
     */
    public function getMessages(array $ids): array {
        $this->connect();
        $mails = [];
        foreach ($ids as $id) {
            if($this->method == "imap"){
                $overview = imap_fetch_overview($this->connection, $id, 0);
                $message = imap_fetchbody($this->connection, $id, 1);
                $mails[$id] = [
                    'headers' => [
                        'from' => $overview[0]->from,
                        'to' => $overview[0]->to,
                        'subject' => $overview[0]->subject,
                        'date' => date("d F, Y", strtotime($overview[0]->date)),
                    ],
                    'ts' => strtotime($overview[0]->date),
                    'message' => trim(quoted_printable_decode($message)),
                ];
            }
            elseif($this->method == "curl"){
                $boundary = null;
                $body = null;
                $mailHeaders = [];

                try {
                    $this->setCurlURL(';UID='.$id.';SECTION=HEADER');
                    $headers = $this->getCurlResponse();

                    // detect boundary
                    if(preg_match('#boundary="([^"]+)"#Ui', $headers, $pboundary)){
                        $boundary = $pboundary[1];
                    }

                    // parse mail headers
                    $headersExp = explode("\n", $headers);

                    foreach ($headersExp as $header) {
                        $header = trim($header);
                        if(strlen($header) == 0 || stripos($header, ': ') === false) continue;

                        $headExp = explode(': ', $header, 2);
                        $key = strtolower(trim($headExp[0]));
                        $value = trim($headExp[1]);
                        $mailHeaders[$key] = $value;
                    }
                } catch (BaseException $e){
                }

                try {
                    $this->setCurlURL(';UID='.$id.';SECTION=TEXT');
                    $body = $this->getCurlResponse();
                } catch (BaseException $e){
                }

                $mails[$id] = [
                    'boundary' => $boundary,
                    'headers' => $mailHeaders,
                    'ts' => (isset($mailHeaders['date']) ? strtotime($mailHeaders['date']) : -1),
                    'message' => quoted_printable_decode($body),
                ];
            }
        }

        return $mails;
    }


    /**
     * Чтение писем с сервера
     * @param string $filter Фильтр сообщений
     *                      ALL - все письма
     *                      FROM "mail@mail.com" - письма от конкретного отправителя
     */
    public function getInput(string $filter = 'ALL'): array {
        $ids = $this->search($filter);
        $mails = $this->getMessages($ids);
        $this->close();

        return $mails;
    }

    private function connect(){
        if($this->method == "imap"){
            $this->createImapConnection();
        }
        elseif($this->method == "curl"){
            $this->createCurlConnection();
        }

        return $this->connection;
    }

    private function createImapConnection(){
        if(!function_exists('imap_open')){
            throw new BaseException('MailReceiver: IMAP extension does not configurated');
        }     

        if($this->connection !== false) return $this->connection;

        $this->connection = imap_open('{'.$this->imapServer.':'.$this->imapPort.'/imap/ssl}' . $this->mailbox, $this->login, $this->password);  

        if(!$this->connection){
            throw new BaseException('IMAP connection error: ' . imap_last_error(), 0, [
                'server' => $this->imapServer,
                'port' => $this->imapPort,
                'login' => $this->login,
                'password' => $this->password,
                'via' => $this->method,
            ]);
        }

        return $this->connection;
    }

    private function createCurlConnection(){
        if (!function_exists('curl_init')){
            throw new BaseException('MailReceiver: curl extension does not configurated');
        }

        if($this->connection !== false) return $this->connection;

        $this->connection = curl_init();
        curl_setopt($this->connection, CURLOPT_USERNAME, $this->login);
        curl_setopt($this->connection, CURLOPT_PASSWORD, $this->password);
        curl_setopt_array($this->connection, $this->chParams);

        return $this->connection;
    }

    private function setCurlURL(string $append){
        $url = 'imaps://' . $this->imapServer . ':' . $this->imapPort . '/' . $this->mailbox . $append;
        curl_setopt($this->connection, CURLOPT_URL, $url);
    }

	public function close(){
        if(!$this->connection) return;

        if($this->method == "imap"){
            imap_close($this->connection);
        } else {
            curl_close($this->connection);
        }

        $this->connection = false;
	}

    private function getCurlResponse(){
        $response = curl_exec($this->connection);
        if(!$response){
            $error = curl_error($this->connection);
            $errno = curl_errno($this->connection);
            throw new BaseException('MailReceiver: curl returned error ('.$errno.'): ' . $error, $errno, [
                'info' => curl_getinfo($this->connection)
            ]);
        }

        return $response;
    }
}