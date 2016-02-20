<?php
require '../vendor/autoload.php';

$server = new \Jivoo\Http\SapiServer(function ($request, $response) {
    $response->getBody()->write('Hello, World!');
    return $response;
});

$server->add(function ($request, $response, $next) {
    $response->getBody()->write('<strong>');
    $response = $next($request, $response);
    $response->getBody()->write('</strong>');
    return $response;
});

$server->add(function ($request, $response, $next) {
    $response->getBody()->write('<!DOCTYPE html><html><body>');
    $response = $next($request, $response);
    $response->getBody()->write('</body></html>');
    return $response;
});

$server->listen();
