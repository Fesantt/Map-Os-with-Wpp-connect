<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function insertToken($data) {
        return $this->db->insert('whatsapp', $data);
    }

    public function updateSessionStatus($session, $status) {
        $this->db->where('name', $session);
        return $this->db->update('whatsapp', ['isActive' => $status]);
    }

    public function updateSessionQRCode($sessionName, $qrCodeBase64, $isActive) {
        $this->db->where('name', $sessionName);
    
        $data = [
            'qrCodeBase64' => $qrCodeBase64,
            'isActive' => $isActive
        ];
    
        return $this->db->update('whatsapp', $data);
    }
    
    public function getSessionByName($name) {
        $this->db->where('name', $name);
        return $this->db->get('whatsapp')->row_array();
    }

    public function getAllSessions() {
        $query = $this->db->get('whatsapp');
        return $query->result_array();
    }
    
    public function updateConnectionStatus($sessionId, $isConnected) {
        $this->db->set('isConnected', $isConnected);
        $this->db->where('name', $sessionId);
        return $this->db->update('whatsapp');
    }
}
