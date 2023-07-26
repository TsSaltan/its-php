<?php 
namespace tsframe\module;

use Error;
use Stripe\StripeClient;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;

class Stripe {
    public static function getPrivateKey(): ?string {
        return Config::get('stripe.private_key');
    }

    public static function getSuccessURI(array $params = []): string {
        return Http::makeURI('/stripe-payment/success', $params);
    }

    public static function getCancelURI(): string {
        return Http::makeURI('/stripe-payment/cancel');
    }

    private $client;

    public function __construct(){
        $this->client = new StripeClient(self::getPrivateKey());
    }

    /**
     * Top up user's balance
     * @param  SingleUser $user   
     * @param  float      $amount 
     * @return string     Redirect URI for continue checkout
     */
    public function paymentToUserBalance(SingleUser $user, float $amount): string {
        $currency = Cash::getCurrency();
        $customer = $this->createCustomer($user);
        $session = $this->client->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => 'Top up account\'s balance (' . $user->get('email') . ')',
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'customer' => $customer->id,
            'mode' => 'payment',
            'success_url' => self::getSuccessURI(['session_id' => '{CHECKOUT_SESSION_ID}']),
            'cancel_url' => self::getCancelURI(),
        ]);

        return $session->url;
    }

    public function createCustomer(SingleUser $user){
        return $this->client->customers->create([
            'name' => $user->get('login'),
            'phone' => $user->getMeta()->get('phone'),
            'metadata' => ['user_id' => $user->get('id')],
            'email' => $user->get('email')
        ]);
    }

    public function checkPayment(string $sessionId){
        $session = $this->client->checkout->sessions->retrieve($sessionId);
        $customer = $this->client->customers->retrieve($session->customer);

        return ['session' => $session, 'customer' => $customer];
    }
}
?>