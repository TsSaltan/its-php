<?php
namespace tsframe\module;

class PayssionModule {
    /**
     * @var PayssionClient
     */
    private static $client;

    public static function getClient(): PayssionClient {
        if(!is_object(self::$client)){
            self::$client = new PayssionClient(
                Config::get('payssion.api_key'),
                Config::get('payssion.secret_key'),
                (intval(Config::get('payssion.production_mode')) == 1),
            );
        }

        return self::$client;
    }

    public static function createPayment(){
        
    }
}