<?php

/**
 * This source code is an adaptation of RecordSet:
 * 
 * Title: RecordSet Class
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/4/page/4
 */

/**
 * Abstract class to set records
 */
abstract class RecordSet {

    /**
     * Protected var to hold database instance
     * @var Mixed
     */
    protected $db;

    /**
     * Protected var to hold PDO statement
     * @var Mixed
     */
    protected $stmt;

    /**
     * Protected var to hold SQL query
     * @var String
     */
    protected $sql;

    /**
     * Protected var to hold element name
     * @var String
     */
    protected $elementName;

    /**
     * Protected var to hold params
     * @var Array
     */
    protected $params;

    /**
     * Class construct that instantiates database connection
     */
    public function __construct () {
        $this->db = Database::conn();
    }

    /**
     * Method to initialise record set
     * @param String $sql - SQL query
     * @param String $elementName - element name
     * @param Mixed $params - binding parameters
     */
    public function init ($sql, $elementName, $params = null) {
        try {
            // Set sql
            $this->sql = $sql;
            // Set element name
            $this->elementName = $elementName;
            // Set params
            $this->params = $params;
            // Check if passed $params is an array
            if (is_array($this->params)) {
                // Prepare the statement
                $this->stmt = $this->db->prepare($this->sql);
                // Exectute the statement
                $this->stmt->execute($this->params);
            } else {
                // Query database without binding parameters
                $this->stmt = $this->db->query($this->sql);
            }
        } catch (PDOException $e) {
            // Create instance of Logger and pass the log path and extention
            $logger = new Logger('logs/error_log', 'txt');
            // Write to error log file
            $logger->write('[' . date('Y-m-d, h:i:s') , '] Error: ' . $e->getMessage());
        }
    }
}

