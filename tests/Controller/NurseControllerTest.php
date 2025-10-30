<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\NurseController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class NurseControllerTest extends TestCase
{
    public function testIndexReturnsArray(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $repo->method('findAll')->willReturn([]);

    $controller = new NurseController();
    // Provide a minimal container so AbstractController methods like json() won't access an uninitialized container
    $controller->setContainer($this->createMock(\Psr\Container\ContainerInterface::class));
        $response = $controller->getAll($repo);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(0, $data);
    }

    public function testGetNurseByIdNotFound(): void
    {
    $repo = $this->createMock(UserRepository::class);
    $repo->method('find')->with(999999)->willReturn(null);

    $controller = new NurseController();
    $controller->setContainer($this->createMock(\Psr\Container\ContainerInterface::class));
    $response = $controller->getNurseById(999999, $repo);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testFindByNameNotFound(): void
    {
        // UserRepository does not declare a findOneByName method explicitly; allow it on the mock
        $repo = $this->getMockBuilder(UserRepository::class)
            ->addMethods(['findOneByName'])
            ->disableOriginalConstructor()
            ->getMock();
        $repo->method('findOneByName')->with('no-name')->willReturn(null);

        $controller = new NurseController();
        $controller->setContainer($this->createMock(\Psr\Container\ContainerInterface::class));
        $response = $controller->findByName('no-name', $repo);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testLoginInvalidCredentials(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $repo->method('findOneBy')->with(['user' => 'no-such-user'])->willReturn(null);

    $controller = new NurseController();
    $controller->setContainer($this->createMock(\Psr\Container\ContainerInterface::class));
    $request = new Request([], [], [], [], [], [], json_encode(['user' => 'no-such-user', 'password' => 'wrong']));
    $response = $controller->login($request, $repo);

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }
}
