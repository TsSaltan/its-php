<?php 
namespace tsframe\module;

use Error;
use Exception;
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
        (new Logger('cash-stripe'))->debug('Initial top up balance of account #' . $user->get('id'), ['login' => $user->get('login'), 'amount' => $amount . ' ' . $currency, 'customer_id' => $customer->id]);
        try {
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
            (new Logger('cash-stripe'))->debug('Created session for replenishment account #' . $user->get('id'), ['login' => $user->get('login'), 'amount' => $amount . ' ' . $currency, 'customer_id' => $customer->id, 'session' => $session]);
        } catch (\Error $e){
            (new Logger('cash-stripe'))->error('Error on creating payment session: ' . $e->getMessage() , ['user_id' => $user->get('id'), 'login' => $user->get('login'), 'amount' => $amount . ' ' . $currency, 'customer_id' => $customer->id]);
            return Http::makeURI('/dashboard/');
        }

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

    public function checkPayment(string $sessionId, SingleUser $user): bool {
        $cash = new Cash($user);
        try {    
            $session = $this->client->checkout->sessions->retrieve($sessionId);
            $customer = $this->client->customers->retrieve($session->customer);
            $trId = $session->payment_intent;
            if(strtolower($session->currency) != strtolower(Cash::getCurrency())){
                (new Logger('cash-stripe'))->alert('System currency and Stripe currency not equal' , ['stripe_payment_currency' => $session->currency, 'system_currency' => Cash::getCurrency(), 'user_id' => $user->get('id'), 'payment_intent' => $trId]);
                return false;
            }

            if($session->payment_status == 'paid' && $session->status == 'complete'){
                if($customer->metadata->user_id != $user->get('id')){
                    (new Logger('cash-stripe'))->error('Invalid current user id and user id from accepted payment' , ['customer_user_id' => $customer->metadata->user_id, 'user_id' => $user->get('id'), 'payment_intent' => $trId]);
                    return false;
                }

                if(!$cash->isTransactionExists($trId)){
                    $cash->add($session->amount_total / 100, 'Account replenishment (online via Stripe)', $trId);
                    (new Logger('cash-stripe'))->info('Account balance replenishment' , ['user_id' => $user->get('id'), 'payment_intent' => $trId, 'amount' => ($session->amount_total / 100)]);
                    return true;
                }
            } else {
                (new Logger('cash-stripe'))->debug('Payment cancelled' , ['user_id' => $user->get('id'), 'payment_intent' => $trId, 'session' => $session]);
            }
        } catch (\Error | \Exception  $e){
            (new Logger('cash-stripe'))->error('Error on account replanishment' , ['user_id' => $user->get('id'), 'error' => $e->getMessage()]);
        }
        
        return false;
    }
}
?>