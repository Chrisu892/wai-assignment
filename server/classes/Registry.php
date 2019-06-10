<?php

/**
 * This source code is an adaptation of Registry class:
 * 
 * Title: Registry
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/4/page/4
 */

/**
 * Abstract class registry
 * Acts like an interface that defines methods that need to be overriden
 */
abstract class Registry {
    private function __construct () {}
    abstract protected function get ($key);
    abstract protected function set ($key, $value);
}