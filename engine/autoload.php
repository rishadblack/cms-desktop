<?php
define('DEBUG', true);

date_default_timezone_set('Asia/Dhaka');

if (DEBUG) {
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
}

include_once dirname(__FILE__) . "/../vendor/autoload.php";

Session::startSession();

$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
$url = $protocol.$_SERVER['HTTP_HOST'];

$application = new MainEngine();