<?php

echo '111';
require_once "vendor/autoload.php";

$class = new \Suman\Egnyte\SkeletonClass();

echo $class->echoPhrase("It's working");