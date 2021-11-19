<?php
define('GENERATED_NAME', 'phpunit-'.uniqid());
define('GENERATED_NAME_SYSTEM', '.phpunit-'.uniqid());
define('GENERATED_NAME_UPPER', getRandomString(8));
define('GENERATED_EMAIL', 'phpunit-'.uniqid().'@test.com');

function getRandomString($length = 8)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}
