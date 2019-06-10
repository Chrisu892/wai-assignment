<?php

/**
 * This source code is an adaptation of Database class:
 * 
 * Title: PDO Connection Class
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/3/page/3
 */

 /**
  * Database class
  */
abstract class Database {

    /**
     * Protected static database connection
     * @var Mixed database connection
     */
    protected static $conn = null;

    /**
     * Method to connect to database
     */
    public static function conn () {
        // Grab the dns details
        $dns = AppRegistry::getValue('dns');
        // Grab the database user
        $user = AppRegistry::getValue('user');
        // Grab the database password
        $pass = AppRegistry::getValue('pass');

        // Check if connection exists
        if (!self::$conn) {
            try {
                // Connect to database
                self::$conn = new PDO($dns, $user, $pass);
                // Set PDO error modes
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Set default fetch mode
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }
            // Catch exceptions
            catch (PDOExeption $e) {
                // Create instance of Logger and pass the log path and extention
                $logger = new Logger('logs/error_log', 'txt');
                // Write to error log file
                $logger->write('[' . date('Y-m-d, h:i:s') , '] Error: ' . $e->getMessage());
            }
        }
        // Return connection
        return self::$conn;
    }

}