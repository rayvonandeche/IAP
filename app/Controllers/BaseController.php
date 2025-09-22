<?php
abstract class BaseController {
    protected function render($view, $data = []) {
        extract($data);

        ob_start();
        include __DIR__ . "/../Views/$view.php";
        $content = ob_get_clean();

        include __DIR__ . "/../Views/layouts/main.php";
    }
}