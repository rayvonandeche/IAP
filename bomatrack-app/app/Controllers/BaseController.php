<?php

abstract class BaseController {
    protected function render($view, $data = []) {
        extract($data);

        // Start output buffering to capture the view content
        ob_start();
        include __DIR__ . "/../Views/{$view}.php";
        $content = ob_get_clean();

        // Include the main layout with the captured content
        include __DIR__ . '/../Views/layouts/main.php';
    }
}

?>