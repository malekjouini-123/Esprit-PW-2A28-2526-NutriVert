<?php
class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
