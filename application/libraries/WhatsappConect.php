<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class WhatsappConect {
    protected $CI;
    // private $endpoint = "http://ztech.br:9090/api"; 
    // private $secretKey = "123456";  

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->helper('url');
        $this->endpoint = $_ENV['WHATSAPP_ENDPOINT'];
        $this->secretKey = $_ENV['WHATSAPP_SECRET_KEY'];
    }

    public function generateToken($identifier) {

        $url = "{$this->endpoint}/{$identifier}/{$this->secretKey}/generate-token";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

    public function startSession($identifier, $token) {

        $url = "{$this->endpoint}/{$identifier}/start-session";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); 
    
        $headers = [
            'Authorization: Bearer '.$token 
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }
    
    public function getQrCode($identifier, $token) {

        $url = "{$this->endpoint}/{$identifier}/start-session";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
    
        $headers = [
            'Authorization: Bearer '.$token 
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

    public function checkStatus($identifier, $token) {

        $url = "{$this->endpoint}/{$identifier}/check-connection-session";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
    
        $headers = [
            'Authorization: Bearer '.$token
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

    public function checkNumber($identifier, $token, $phone) {

        $url = "{$this->endpoint}/{$identifier}/check-number-status/". $phone;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
    
        $headers = [
            'Authorization: Bearer '.$token
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }

    public function sendMessageText($identifier, $token, $phone, $text) {

        $url = "{$this->endpoint}/{$identifier}/send-message";
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
    
        
        $body = [
            'phone' => $phone,
            'message' => $text,
        ];
        $jsonBody = json_encode($body);
    
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
    
        $headers = [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ];
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);
    
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            return false;
        }
    }
}
