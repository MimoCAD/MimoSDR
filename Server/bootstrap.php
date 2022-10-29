<?php
namespace TTG;
require_once __DIR__.'/autoload.php';

if (isset($_GET['debug']))
{
    error_reporting(E_ALL);
    ini_set('display_errors', 'stdout');
}

session_start();
