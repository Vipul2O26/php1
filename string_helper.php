<?php

class StringHelper {

    public static function upperCase($text){
        return strtoupper($text);
    }

    public static function lowerCase($text){
        return strtolower($text);
    }

    public static function camelCase($text){
        return ucwords(strtolower($text));
    }
}
