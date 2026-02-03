<?php

require_once __DIR__ . '/helper/string_helper.php';
require_once __DIR__ . '/helper/array_helper.php';

// STRING HELPERS
$text = "hello this string will CONVERT SMALL into uppercase using string helper";

echo "Original: $text<br><br>";

echo "Uppercase:<br>";
echo StringHelper::upperCase($text);

echo "<br><br>Lowercase:<br>";
echo StringHelper::lowerCase($text);

echo "<br><br>Camel Case:<br>";
echo StringHelper::camelCase($text);


// ARRAY HELPERS
$arr1 = ["a" => 1, "b" => 2];
$arr2 = ["b" => 3, "c" => 4];

echo "<br><br>Array Merge:<br>";
print_r(ArrayHelper::merge($arr1, $arr2));


