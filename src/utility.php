<?php

/**
 * Class Utility
 */
class Utility {
    /**
     * Return file extension
     *
     * @param $file
     * @return mixed
     */
    public static function get_file_type($file) {
        $temp = explode('.',$file);
        return end($temp);
    }

    /**
     * get class name of version file
     * @param $file
     * @return mixed
     */
    public static function get_class_name($file) {
        $temp = explode('.',$file);
        return $temp[0];
    }

    /**
     * output a list
     * @param $list
     */
    public static function show_list($list) {
        foreach($list as $file_name)
            echo "\t" . $file_name . "\n";
    }

    /**
     * return current file directory
     *
     * @return string
     */
    public static function current_dir() {
        return dirname(__FILE__);
    }
}