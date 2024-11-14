<?php

class PaypalPro {
    private $apiUsername = 'sb-47vhkg31203523_api1.business.example.com';
    private $apiPassword = '4ENR3S6NBK7WSXTP';
    private $apiSignature = 'AgeFVB3HLVg6U0l5a5QVoq0mk0xQAVYyhtLN8KGaHssrpG9Q9sQAu6by';
    private $apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    private $version = '204.0';

    public function paypalCall($methodName, $nvpStr) {
        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName&VERSION=" . urlencode($this->version) . "&PWD=" . urlencode($this->apiPassword) . "&USER=" . urlencode($this->apiUsername) . "&SIGNATURE=" . urlencode($this->apiSignature) . "$nvpStr";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $httpResponse = curl_exec($ch);

        if (!$httpResponse) {
            exit("$methodName failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = [];
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            if (sizeof($tmpAr) > 1) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
            exit("Invalid HTTP Response for POST request($nvpreq) to $this->apiEndpoint.");
        }

        return $httpParsedResponseAr;
    }

    public function doDirectPayment($paymentData) {
        $nvpStr = "&PAYMENTACTION=Sale&IPADDRESS=" . urlencode($_SERVER['REMOTE_ADDR']);
        foreach ($paymentData as $key => $value) {
            $nvpStr .= "&" . strtoupper($key) . "=" . urlencode($value);
        }
        return $this->paypalCall("DoDirectPayment", $nvpStr);
    }
}
