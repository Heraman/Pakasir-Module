<?php

class Pakasir
{
    private $apiKey;

    private $projectSlug;

    private $baseUrl = 'https://app.pakasir.com';

    public function __construct($apiKey, $projectSlug)
    {
        $this->apiKey = $apiKey;
        $this->projectSlug = $projectSlug;
    }

    public function createPaymentUrl($amount, $orderId, $options = [])
    {
        $url = $this->baseUrl."/pay/{$this->projectSlug}/".intval($amount);

        $queryParams = [
            'order_id' => $orderId,
        ];

        if (isset($options['redirect'])) {
            $queryParams['redirect'] = $options['redirect'];
        }
        if (isset($options['qris_only']) && $options['qris_only']) {
            $queryParams['qris_only'] = 1;
        }

        return $url.'?'.http_build_query($queryParams);
    }

    public function createTransaction($amount, $orderId, $paymentMethod = 'qris')
    {
        $endpoint = "/api/transactioncreate/{$paymentMethod}";

        $payload = [
            'project' => $this->projectSlug,
            'order_id' => $orderId,
            'amount' => intval($amount),
            'api_key' => $this->apiKey,
        ];

        return $this->sendRequest('POST', $endpoint, $payload);
    }

    public function simulatePayment($amount, $orderId)
    {
        $endpoint = '/api/paymentsimulation';

        $payload = [
            'project' => $this->projectSlug,
            'order_id' => $orderId,
            'amount' => intval($amount),
            'api_key' => $this->apiKey,
        ];

        return $this->sendRequest('POST', $endpoint, $payload);
    }

    public function cancelTransaction($amount, $orderId)
    {
        $endpoint = '/api/transactioncancel';

        $payload = [
            'project' => $this->projectSlug,
            'order_id' => $orderId,
            'amount' => intval($amount),
            'api_key' => $this->apiKey,
        ];

        return $this->sendRequest('POST', $endpoint, $payload);
    }

    public function getTransactionDetail($amount, $orderId)
    {
        $endpoint = '/api/transactiondetail';
        $queryParams = [
            'project' => $this->projectSlug,
            'amount' => intval($amount),
            'order_id' => $orderId,
            'api_key' => $this->apiKey,
        ];

        return $this->sendRequest('GET', $endpoint, $queryParams);
    }

    private function sendRequest($method, $path, $data)
    {
        $url = $this->baseUrl.$path;
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
        ];

        if ($method === 'GET') {
            $url .= '?'.http_build_query($data);
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['status' => 'error', 'message' => curl_error($ch)];
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
