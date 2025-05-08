<?php
// src/api/v1/controllers/SimpleController.php
namespace Api\V1\Controllers;
use Api\BaseApiController;

class SimpleController extends BaseApiController {
    public function index() {
        $this->response(['message' => 'V1 API test successful']);
    }
    
    /**
     * Vrátí náhodné číslo od 0 do 99999 (maximálně pětimístné)
     * 
     * @return int Náhodné číslo
     */
    public function getRandomNumber() {
        // Generuje číslo od 0 do 99999
        return mt_rand(0, 99999);
    }
    
    /**
     * API endpoint pro získání náhodného čísla
     */
    public function randomNumber() {
        $this->response([
            'number' => $this->getRandomNumber(),
            'timestamp' => time()
        ]);
    }
}