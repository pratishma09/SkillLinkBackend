<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class PaymentService  implements PaymentGatewayInterface
{
    public $amount;
    public $base_url;

    public $purchase_order_id;
    public $purchase_order_name;

    public $inquiry_response;

    /*
    |--------------------------------------------------------------------------
    | Customer Detail
    |--------------------------------------------------------------------------
    | 
    */
    public $customer_name;
    public $customer_phone;
    public $customer_email;

    public function __construct()
    {
        $this->base_url = env('APP_DEBUG') ? 'https://a.khalti.com/api/v2/' : 'https://khalti.com/api/v2/';
    }

    public function byCustomer($name,$email,$phone){
        $this->customer_name = $name;
        $this->customer_email = $email;
        $this->customer_phone = $phone;
        return $this;
    }

    /**
     *
     * Function to perform some logic before payment process
     */
    public function pay(float $amount, $return_url, $purchase_order_id, $purchase_order_name)
    {
        $this->purchase_order_id = $purchase_order_id;
        $this->purchase_order_name = $purchase_order_name;
        return $this->initiate($amount, $return_url);
    }

    /**
     *
     * Initiate Payment Gateway Transaction
     * @param float amount : Amount requested for payment transaction
     * @param return_url : Redirect url after payment transaction
     * @param array arguments : Additional dataset
     *
     */
    public function initiate(float $amount, $return_url, ?array $arguments = null)
    {
        $this->amount = env('APP_DEBUG') ? 1000 : ($amount * 100);
        $process_url = $this->base_url . 'epayment/initiate/';

        $return_url = $return_url;
        $website_url = url('/');
        $purchase_order_id = $this->purchase_order_id;
        $purchase_order_name = $this->purchase_order_name;
        $customer_name = $this->customer_name;
        $customer_email = $this->customer_email;
        $customer_phone = $this->customer_phone;

        // Build the data array
        $data = [
            "return_url" => $return_url,
            "website_url" => $website_url,
            "amount" =>  $this->amount,
            "purchase_order_id" => $purchase_order_id,
            "purchase_order_name" => $purchase_order_name,
            "customer_info" => [
                "name" => $customer_name,
                "email" => $customer_email,
                "phone" => $customer_phone
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'key ' . env('KHALTI_SECRET_KEY'), // Replace with your authorization token
        ])->post($process_url, $data);
        if ($response->ok()) {
            $body = json_decode($response->body());
            return redirect()->to($body->payment_url);
        } else {
            throw new Exception('Khalti transaction failed');
        }
    }

    /**
     *
     * Success status of payment transaction 
     * @param array inquiry : Payment transaction response
     * @param array arguments : Additional dataset
     * @return bool 
     *
     */
    public function isSuccess(array $inquiry, ?array $arguments = null): bool
    {
        return ($inquiry['status'] ?? null) == 'Completed';
    }

    /**
     *
     * Requested amount to be registered
     * @param array inquiry : Payment transaction response
     * @param array arguments : Additional dataset
     * @return float 
     *
     */
    public function requestedAmount(array $inquiry, ?array $arguments = null): float
    {
        return $inquiry['total_amount'];
    }

    /**
     *
     * Payment status lookup request
     * @param mixed transaction_id : Code provided by payment gateway vendor to uniquely identify payment transaction 
     * @param array arguments : Additional dataset
     * @return array 
     *
     */
    public function inquiry($transaction_id, ?array $arguments = null) : array
    {
        $process_url = $this->base_url . 'epayment/lookup/';
        $payload = [
            'pidx' => $transaction_id
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'key ' . env('KHALTI_SECRET_KEY'),
        ])->post($process_url, $payload);
        $this->inquiry_response =  json_decode($response->body(),true);
        return $this->inquiry_response;
    }
}