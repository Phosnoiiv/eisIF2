<?php
namespace EverISay\SIF\Backend\Endpoint\Web;

use Spiral\Router\Annotation\Route;

class HelloWorldController {
    #[Route('/hello-world', methods: 'GET')]
    public function index(): string {
        return 'hello world';
    }
}
