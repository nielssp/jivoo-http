<?php
require '../vendor/autoload.php';

$router = new Jivoo\Http\Router();

$server = new \Jivoo\Http\SapiServer($router);

$server->listen();
