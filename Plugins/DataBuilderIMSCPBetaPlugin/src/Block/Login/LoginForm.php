<?php

namespace DataBuilder\Block\Login;

use DataBuilder\Block\AbstractBlock;

/**
 * Login Form Block
 * 
 * Handles login form rendering
 */
class LoginForm extends AbstractBlock
{
    /**
     * Prepare block data
     */
    public function prepare(): self
    {
        // Set default form action
        $this->setData('form_action', '/login.php');
        
        // Get any pre-filled username
        $this->setData('username', $_POST['uname'] ?? '');
        
        // Check if there's an error message
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === false) {
            $this->setData('error', tr('Invalid username or password'));
        }
        
        return $this;
    }
}
