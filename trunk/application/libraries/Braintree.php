<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Parse
 *
 * Serves as a generator for all relevant parse classes.
 *
 */
//namespace Braintree;

require_once 'braintree/lib/autoload.php';
require_once 'braintree/lib/Braintree.php';

use Braintree\Configuration;
use Braintree\Transaction;
use Braintree\ClientToken;
use Braintree\PaymentMethod;

class Braintree {

    public function __construct() {
        Configuration::environment('sandbox');
        Configuration::merchantId('vwd99xx3htdq5kb5');
        Configuration::publicKey('g3vsbsvsv5hf6krh');
        Configuration::privateKey('f9197eaa06a09c690933999c597271d4');
    }
    
    
    public function clientToken(){
        return ClientToken::generate();
    }
    
    
    public function createrTransaction($amount, $paymentMethodNonce){
        
        $result = Transaction::sale([
                    'amount' => $amount,
                    'paymentMethodNonce' => $paymentMethodNonce,
//                    'options' => [ 'submitForSettlement' => true]
        ]);
        if ($result->success) {
            $data = array(
                'result' => true,
                'data' => [
                    'message' => 'Success',
                    'transaction_id' => $result->transaction->id,
                ]
            );
        } else if ($result->transaction) {
            $data = array(
                'result' => false,
                'data' => [
                    'code' => $result->transaction->processorResponseCode,
                    'message' => 'Error processing transaction: ' . $result->transaction->processorResponseText,
                ]
            );
        } else {
            $data = array(
                'result' => false,
                'data' => [
                    'code' => $result->errors->deepAll()[0]->code,
                    'message' => 'Validation errors: ' . $result->errors->deepAll()[0]->message,
                ]
            );

        }
        return $data;
    }


}
