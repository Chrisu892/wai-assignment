<?php

/**
 * This source code is an adaptation of JSONRecordSet:
 * 
 * Title: JSONRecordSet
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/4/page/4
 */

/**
 * JSON Record Set class
 * This class handles get, insert, delete and update actions on the database
 */
class JSONRecordSet extends RecordSet {

    /**
     * Protected variable to hold data
     * @var Array
     */
    protected $data;

    /**
     * Protected variable to hold the row count
     * @var Number
     */
    protected $count;

    /**
     * Method to get rows from database
     * @return String JSON
     */
    public function get ($sql, $elementName, $params = null) {
        // Get reference to the parent class
        parent::init($sql, $elementName, $params);
        // Fetch records
        $records = $this->stmt->fetchAll();
        // Count returned records
        $this->count = count($records);
        // Set the data array
        $this->data = [
            // The status, either error or success
            "status" => $this->_getStatus(),
            // The message
            "message" => $this->_getMessage(),
            // The element name
            "$this->elementName" => [
                // The count of returned results
                "count" => $this->count,
                // Actual results
                "result" => $records
            ]
        ];
        // Return JSON
        return $this->_toJSON();
    }

    /**
     * Method to delete row from database
     * @return String JSON
     */
    public function delete ($sql, $elementName, $params = null) {
        // Get the reference to the parent class
        parent::init($sql, $elementName, $params);
        // At this time, only one row is expected to be deleted
        // @todo: multiple delete actions would increment this count $this->count++ ?
        $this->count = 1;
        // Set the data array
        $this->data = [
            // Get the status
            "status" => $this->_getStatus(),
            // The message
            "message" => "Deleted 1 row from $this->elementName table."
        ];
        // Return JSON
        return $this->_toJSON();
    }

    /**
     * Method to update row in database
     * @return String JSON
     */
    public function update ($sql, $elementName, $params = null) {
        // Get the reference to the parent class
        parent::init($sql, $elementName, $params);
        // At this time only one row is expected to be updated
        // @todo: multiple update actions could increment the count
        $this->count = 1;
        // Set the data array
        $this->data = [
            // Get the status
            "status" => $this->_getStatus(),
            // The message
            "message" => "Updated 1 row in $this->elementName table."
        ];
        // Return JSON
        return $this->_toJSON();
    }

    /**
     * Method to insert row into database table
     * @return String JSON
     */
    public function insert ($sql, $elementName, $params = null) {
        // Get the reference to the parent class
        parent::init($sql, $elementName, $params);
        // Set the count to one
        $this->count = 1;
        // Create the data array
        $this->data = [
            // Get the status
            "status" => $this->_getStatus(),
            // The message
            "message" => "Inserted 1 row into $this->elementName table."
        ];
        // Return JSON
        return $this->_toJSON();
    }

    /**
     * Method to get the right status
     * @return String
     */
    private function _getStatus () {
        return $this->count == 0 ? "error" : "success";
    }

    /**
     * Method to get the message
     * @return String
     */
    private function _getMessage() {
        return $this->count == 0 ? "No records found." : "Found $this->count " . ($this->count > 0 ? $this->elementName : rtrim($this->elementName, 's'));
    }

    /**
     * Method to encide array into JSON
     * @return String
     */
    private function _toJSON () {
        return json_encode($this->data, JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT);
    }
}