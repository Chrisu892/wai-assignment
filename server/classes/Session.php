<?php

/**
 * This source code is an adaptation of:
 * 
 * Title: Managing Sessions with Classes
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/8/page/6
 */

/**
 * Session class
 */
class Session extends Registry {

    /**
     * Private static instance
     * @var Object
     */
    private static $instance;

    /**
     * Class construct
     * Start session
     */
    private function __construct () {
        // start the session
        session_start();
    }

    /**
     * Method to initialise the session
     */
    public static function init () {
        // Check if instance was not set
        if (!isset(self::$instance)) {
            // Instantiate new session object and assign it to the instance
            self::$instance = new Session;
        }
        // Return the instance
        return self::$instance;
    }

    /**
     * Method to set the session value
     */
    public function set ($k, $v) {
        $_SESSION[$k] = $v;
    }

    /**
     * Method to get the session key
     */
    public function get ($k) {
        return isset($_SESSION[$k]) ? $_SESSION[$k] : null;
    }

}