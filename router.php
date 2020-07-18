<?php

class Router {

    protected $route;

    public function route($route)
    {
        switch ($route) {
            case '/':
                home();
                break;
            case '/success':
                callback();
                break;
            case '/register':
                register();
                break;
            case '/auth':
                auth();
                break;
            case '/auth-success':
                authSuccess();
                break;
            case '/token':
                token();
                break;
            case '/me':
                me();
                break;
        }
    }
}