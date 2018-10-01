<?php
namespace tsframe\module;

class Paginator{
	/**
	 * Данные для отображения
	 * @var array
	 */
	protected $data = [];

	/**
	 * Текущая страница
	 * @var int
	 */
	protected $page;

	/**
	 * Количество элементов на странице
	 * @var int
	 */
	protected $num;

	/**
	 * Номер последней страницы
	 * @var int
	 */
	protected $last;

	/**
	 * Отступ
	 * @var int
	 */
	protected $offset;

	/**
	 * Если нужно получить данные перед отображением, нужно установить данный коллбэк
	 * @var boolean
	 */
	protected $getDataCallback = false;

	public function __construct(array $data, int $num = 20){
		$this->last = ceil(sizeof($data) / $num);
		$this->page = min(max($_GET['page'] ?? 1, 1), $this->last);
		$this->num = $num;
		$this->data = $data;
		$this->offset = ($this->page-1) * $this->num;

	}	

	/**
	 * Есть ли данные для отображения
	 * @return bool
	 */
	public function isData(): bool {
		return sizeof($this->getCurrentSlice()) > 0;
	}

	/**
	 * Установить callback функцию для получения данных
	 * @param callable $callback function($currentValue, $currentKey)
	 */
	public function setDataCallback(callable $callback){
		$this->getDataCallback = $callback;
	}

	/**
	 * Получить "срез" массива - данные которые будут отображены на текущей странице
	 * @return array
	 */
	protected function getCurrentSlice(): array {
		return array_slice($this->data, $this->offset, $this->num);
	}

	/**
	 * Получить массив данных для отображения
	 * @return array
	 */
	public function getData(): array {
		$data = $this->getCurrentSlice();
		if(is_callable($this->getDataCallback)){
			foreach ($data as $key => $value) {
				$data[$key] = call_user_func($this->getDataCallback, $value, $key);
			}
		}

		return $data;
	}

	public function hasPages(): bool {
		return sizeof($this->getPages(10, false)) > 1;
	}

	public function getPages(int $pagesNum = 5, bool $helpers = true): array {
		if(sizeof($this->data) == 0) return [];
		
		$pages = [];

		if($helpers){
			$pages[] = ['title' => '<<', 'url' => $this->makeURI(1), 'current' => $this->page == 1];
			$pages[] = ['title' => '<', 'url' => $this->makeURI($this->page-1), 'current' => $this->page == 1];
		}

		$start = $this->page-floor($pagesNum/2);
		$startIndex = max($start, 1);
		$end = $this->page+floor($pagesNum/2);
		$endIndex = min($end, $this->last);

		for($index = $startIndex - ($end-$endIndex); $index <= $endIndex + ($startIndex - $start); ++$index){
			if($index < 1 || $index > $this->last) continue;
			$pages[] = ['title' => $index, 'url' => $this->makeURI($index), 'current' => $this->page == $index];
		}

		if($helpers){
			$pages[] = ['title' => '>', 'url' => $this->makeURI($this->page+1), 'current' => $this->page >= $this->last];
			$pages[] = ['title' => '>>', 'url' => $this->makeURI($this->last), 'current' => $this->page >= $this->last];
		}

		return $pages;
	}

	protected function makeURI(int $page): string {
		parse_str($_SERVER['QUERY_STRING'], $query);
		$query['page'] = $page;
		return '?' . http_build_query($query);
	}
}