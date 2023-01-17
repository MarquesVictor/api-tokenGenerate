<?php
header('Content-type: application/json');
require './vendor/autoload.php';

use Aura\Router\RouterContainer;

$routerContainer = new RouterContainer();

$map = $routerContainer->getMap();

$map->get('/', '/token/{id}', function ($request) {
    $id = (int) $request->getAttribute('id');
    $response = new Zend\Diactoros\Response();
    $date = new DateTimeImmutable();
    $token = $date->getTimestamp() * $id;
    $token = substr($token, -6);
    $token = json_encode(array('token' => $token), true);
    $response->getBody()->write($token);

    return $response;
});

$matcher = $routerContainer->getMatcher();

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$route = $matcher->match($request);

if (!$route) {
    echo " No route found";
    exit;
}

foreach ($route->attributes as $key => $val) {
    $request = $request->withAttribute($key, $val);
}

$callable = $route->handler;

// You should consider using https://github.com/auraphp/Aura.Dispatcher than the one line code below.
$response = $callable($request);

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
