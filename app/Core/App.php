<?php
require_once __DIR__ . '/Router.php';

class App {
    protected $router;

    public function __construct(){
        $this->router = new Router();
    }

    public function getRouter() {
        return $this->router;
    }

    public function run() {
        $this->router->dispatch();
    }
}