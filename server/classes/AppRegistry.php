<?php

/**
 * This source code is an adaptation of ApplicationRegistry:
 * 
 * Title: ApplicationRegistry
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/5/page/2
 */

 /**
  * Application registry class that extends registry
  */
class AppRegistry extends Registry {

    /**
     * Private array that will hold application values
     * @var Array $values
     */
    private $values = [];

    /**
     * Singleton instance
     * @var AppRegistry
     */
    private static $instance;

    /**
     * Open system configuration file by calling the private function
     */
    public function __construct () {
        $this->openSystemConfigFile();
    }

    /**
     * Singleton instance
     * @return AppRegistry object self
     */
    private static function instance () {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Return a value for previously set key
     * @param Mixed $key
     * @return Mixed
     */
    protected function get ($key) {
        return (isset($this->values[$key])) 
            ? $this->values[$key] 
            : null; 
    }

    /**
     * Set a value for a key
     * @param String $key
     * @param Mixed $value
     */
    protected function set ($key, $value) {
        $this->values[$key] = $value;
    }

    /**
     * Read system config file and put the values into array
     */
    private function openSystemConfigFile () {
        // Check if configuration file exists
        if (file_exists(FS_ROOT.CONFIG)) {
            // Get the file contents
            $json = file_get_contents(FS_ROOT.CONFIG);
            // Decode the file contents
            $config = json_decode($json, 1);
            // For each configuration key value pair
            foreach ($config as $key => $value) {
                // Add key and value to configuration array
                $this->set($key, trim($value));
            }
        }
    }

    /**
     * Method to provide access to often used values
     */
    public static function getValue ($name) {
        try {
            // Get the value from the instance and return it
            return self::instance()->get($name);
        } 
        catch (Exception $e) {
            // Create instance of Logger and pass the log path and extention
            $logger = new Logger('logs/error_log', 'txt');
            // Write to error log file
            $logger->write('[' . date('Y-m-d, h:i:s') , '] Error: ' . $e->getMessage());
        }
    }

}