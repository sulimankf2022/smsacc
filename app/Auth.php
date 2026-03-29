<?php

namespace App;  

class Auth {

    private $session_name = 'user_session';  

    public function login($username, $password) {
        // Validate credentials and log in the user
    }

    public function logout() {
        // End the user session
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        // Check if user is logged in
        return isset($_SESSION[$this->session_name]);
    }

    public function startSession() {
        // Start a session  
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function generateCSRFToken() {
        // Generate a CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token) {
        // Validate the CSRF token
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
