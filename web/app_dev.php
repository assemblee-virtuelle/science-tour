<?php

use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
  || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
  || !in_array(@$_SERVER['REMOTE_ADDR'], array(
    '127.0.0.1',
    '::1',
    '92.169.190.198',
    '37.166.214.184',
    '37.169.110.180',
    '80.215.76.242',
    '83.156.34.69',
    '80.215.168.24',
    '80.215.13.102',
    '80.215.90.124'
  ))
) {
  header('HTTP/1.0 403 Forbidden');
  exit($_SERVER['REMOTE_ADDR'].' : You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('dev', TRUE);
$kernel->loadClassCache();
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
