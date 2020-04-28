<?php


use Burdock\DokuApi\Container;
use Burdock\DokuApi\Controller\NotFoundController;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function test_initialize(): void
    {
        $path = __DIR__ . '/config.json';
        Container::initialize($path);
        $logger = NotFoundController::getLogger();
        $this->assertTrue($logger instanceof Logger);
        $pdo = NotFoundController::getPDOInstance();
        $this->assertTrue($pdo instanceof PDO);
    }
}
