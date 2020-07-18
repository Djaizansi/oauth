<?php

$route = strtok($_SERVER['REQUEST_URI'], '?');
$router = new Router();

$routing = $router->route($route);

?>