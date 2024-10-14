<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Whatsapp extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('WhatsappConect');
        $this->load->model('Whatsapp_model');
    }

    public function index() {
        $this->data['view'] = 'whatsapp/index';
        $this->data['sessions'] = $this->Whatsapp_model->getAllSessions(); 
        return $this->layout($this->data);
    }
    
    public function getToken() {
        $identifier = $this->input->post('name');
        $response = ['success' => false, 'message' => ''];
    
        if ($this->isSessionExisting($identifier)) {
            $this->initiateExistingSession($identifier);
            $response['success'] = true;
            $response['message'] = "Sessão iniciada com sucesso.";
        } else {
            $newTokenResponse = $this->generateNewToken($identifier);
            $response['success'] = $newTokenResponse['success'];
            $response['message'] = $newTokenResponse['message'];
        }
    
        echo json_encode($response);
    }

    public function refreshQRCode() {
        $identifier = $this->input->post('name');
    
        $existingSession = $this->Whatsapp_model->getSessionByName($identifier);

        if ($existingSession) {
            $response = $this->updateQRCode($existingSession);

            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'message' => "Nenhuma sessão encontrada para o identificador $identifier."]);
        }
    }

    private function isSessionExisting($identifier) {
        return $this->Whatsapp_model->getSessionByName($identifier) !== null;
    }

    private function initiateExistingSession($identifier) {
        $existingSession = $this->Whatsapp_model->getSessionByName($identifier);
        echo "Sessão já existente. Iniciando a sessão...";

        $sessionData = $this->whatsappconect->startSession($existingSession['name'], $existingSession['token']);
        
        if ($sessionData) {
            $this->handleSessionResponse($sessionData, $existingSession['name']);
        } else {
            echo "Falha ao fazer a requisição para iniciar a sessão.";
        }
    }

    private function generateNewToken($identifier) {
        $tokenData = $this->whatsappconect->generateToken($identifier);
        $response = ['success' => false, 'message' => '', 'data' => null];
    
        if ($tokenData && isset($tokenData['status']) && $tokenData['status'] === 'success') {
            $response['success'] = true;
            $response['message'] = "Token gerado com sucesso.";
            $response['data'] = [
                'session' => htmlspecialchars($tokenData['session']),
                'token' => htmlspecialchars($tokenData['token']),
                'full' => htmlspecialchars($tokenData['full']),
            ];
    
            $data = [
                'name' => $tokenData['session'],
                'token' => $tokenData['token'],
                'isActive' => 0,
            ];
    
            if ($this->Whatsapp_model->insertToken($data)) {
                $response['message'] .= " Token salvo com sucesso no banco de dados.";
                $this->initiateNewSession($tokenData);
            } else {
                $response['message'] .= " Falha ao salvar o token no banco de dados.";
            }
        } else {
            $response['message'] = "Falha ao gerar o token. Resposta da API: " . htmlspecialchars(json_encode($tokenData));
        }
    
        return $response;
    }

    private function initiateNewSession($tokenData) {
        $sessionData = $this->whatsappconect->startSession($tokenData['session'], $tokenData['token']);
       
        if ($sessionData) {
            $this->handleSessionResponse($sessionData, $tokenData['session']);
        } else {
            echo "Falha ao fazer a requisição para iniciar a sessão.";
        }
    }

    private function handleSessionResponse($sessionData, $sessionName) {
        if (isset($sessionData['status'])) {
            switch ($sessionData['status']) {
                case 'success':
                    echo "Sessão iniciada com sucesso!";
                    $this->Whatsapp_model->updateSessionStatus($sessionName, 1);
                    break;
                case 'CLOSED':
                    $this->saveQRCode($sessionData['qrcode'], $sessionName, true);
                    break;
                default:
                    echo "Falha ao iniciar a sessão. Resposta da API: " . htmlspecialchars(json_encode($sessionData));
            }
        } else {
            echo "Resposta inesperada da API: " . htmlspecialchars(json_encode($sessionData));
        }
    }

    private function updateQRCode($existingSession) {
        $sessionData = $this->whatsappconect->getQrCode($existingSession['name'], $existingSession['token']);
    
        $response = ['success' => false, 'message' => ''];
    
        if ($sessionData) {
            if (isset($sessionData['status'])) {
                if ($sessionData['status'] == 'QRCODE') {
                    $this->saveQRCode($sessionData['qrcode'], $existingSession['name'], true);
                    
                    $response['success'] = true;
                    $response['message'] = 'QR Code atualizado com sucesso.';
                    $response['qrcode'] = $sessionData['qrcode'];
                } else {
                    $response['message'] = htmlspecialchars(json_encode($sessionData));
                }
            } else {
                $response['message'] = "Status não encontrado na resposta da API.";
            }
        } else {
            $response['message'] = "Falha ao fazer a requisição para atualizar o QR Code.";
        }
    
        return $response;
    }
    
    private function saveQRCode($qrCode, $sessionName, $isActive) {
        $this->Whatsapp_model->updateSessionQRCode($sessionName, $qrCode, $isActive);
    }

    public function check() {
        $data = json_decode($this->input->raw_input_stream, true);
        $identifier = $data['name'];
       
    
        $existingSession = $this->Whatsapp_model->getSessionByName($identifier);
    
        if ($existingSession) {
            $response = $this->whatsappconect->checkStatus($existingSession['name'], $existingSession['token']);
    
            if (isset($response['status']) && $response['status'] === true) {
                $this->Whatsapp_model->updateConnectionStatus($existingSession['name'], 1);
            }
    
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'message' => "Nenhuma sessão encontrada para o identificador $identifier."]);
        }
    }

    //test test test test
    public function checkPhone($identifier, $phoneCheck) {
        
        $identifier = $identifier;
        $phone = $phoneCheck;
    
        $existingSession = $this->Whatsapp_model->getSessionByName($identifier);
    
        if ($existingSession) {
            $response = $this->whatsappconect->checkNumber($existingSession['name'], $existingSession['token'], $phone);
    
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'message' => "Nenhuma sessão encontrada para o identificador $identifier."]);
        }
    }
    
}
