<?php

/**
 * Class to log errors into file
 */
class Logger {

    /**
     * Private var to hold $file name
     * @var String
     */
    private $file = null;

    /**
     * Class construct
     * @return Void
     */
    public function __construct ($file = null, $xtension = null) {
        // Check if file is not null
        if (!is_null($file)) {
            // Assign file name to the class $file property
            $this->file = $file . (!is_null($xtension) ? '.' . $xtension : '.txt');
        }
    }

    /**
     * Public method to write to file
     * @return Void
     */
    public function write ($text = null) {
        // Check if text is not null
        if (!is_null($text)) {
            // Open file
            $tmpFile = fopen($this->file, 'w');
            // Write text to file
            fwrite($tmpFile, $text);
            // Close the file
            fclose($tmpFile);
        }
    }

}