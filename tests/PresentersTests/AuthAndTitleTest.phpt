<?php

namespace FKSDB\Tests\PresentersTests;

use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

// phpcs:disable
$container = require '../Bootstrap.php';

// phpcs:enable

class AuthAndTitleTest extends TestCase
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function testPresenter(): void
    {
        $services = $this->container->findByType(Presenter::class);
        foreach ($services as $presenter) {
            $datum = [];
            $reflection = new \ReflectionClass($this->container->getService($presenter));
            $methods = $reflection->getMethods();
            foreach ($methods as $method) {
                $methodName = $method->getName();
                if (preg_match('/authorized([A-Za-z]+)/', $methodName, $matches)) {
                    $datum[$matches[1]]['auth'] = true;
                }
                if (preg_match('/title([A-Za-z]+)/', $methodName, $matches)) {
                    $datum[$matches[1]]['title'] = true;
                }
                if (preg_match('/action([A-Za-z]+)/', $methodName, $matches)) {
                    $datum[$matches[1]]['action'] = true;
                }
                if (preg_match('/render([A-Za-z]+)/', $methodName, $matches)) {
                    $datum[$matches[1]]['render'] = true;
                }
            }
            foreach ($datum as $action => $methods) {
                Assert::hasKey(
                    'auth',
                    $methods,
                    sprintf('Presenter %s:%s should contains auth method', $reflection->getName(), $action)
                );
                Assert::hasKey(
                    'title',
                    $methods,
                    sprintf('Presenter %s:%s should contains title method', $reflection->getName(), $action)
                );
            }
        }
    }
}

// phpcs:disable
$testCase = new AuthAndTitleTest($container);
$testCase->run();
// phpcs:enable