<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Config\Extensions;

use Nette;
use Tracy\Debugger;


/**
 * Core Nette Framework services.
 *
 * @author     David Grudl
 */
class NetteExtension extends Nette\DI\CompilerExtension {
    public $defaults = array(
        'xhtml' => TRUE,
        'session' => array(
            'iAmUsingBadHost' => NULL,
            'autoStart' => 'smart',  // true|false|smart
            'expiration' => NULL,
        ),
        'application' => array(
            'debugger' => TRUE,
            'errorPresenter' => NULL,
            'catchExceptions' => '%productionMode%',
        ),
        'routing' => array(
            'debugger' => TRUE,
            'routes' => array(), // of [mask => action]
        ),
        'security' => array(
            'debugger' => TRUE,
            'frames' => 'SAMEORIGIN', // X-Frame-Options
            'users' => array(), // of [user => password]
            'roles' => array(), // of [role => parents]
            'resources' => array(), // of [resource => parents]
        ),
        'mailer' => array(
            'smtp' => FALSE,
        ),
        'database' => array(), // of [name => dsn, user, password, debugger, explain, autowired, reflection]
        'forms' => array(
            'messages' => array(),
        ),
        'container' => array(
            'debugger' => FALSE,
        ),
        'debugger' => array(
            'email' => NULL,
            'editor' => NULL,
            'browser' => NULL,
            'strictMode' => NULL,
            'bar' => array(), // of class name
            'blueScreen' => array(), // of callback
        ),
    );

    public function loadConfiguration() {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // application
        $application = $container->addDefinition('application')// no namespace for back compatibility
        ->setClass('Nette\Application\Application')
            ->addSetup('$catchExceptions', [$config['application']['catchExceptions']])
            ->addSetup('$errorPresenter', [$config['application']['errorPresenter']]);

        if ($config['application']['debugger']) {
            $application->addSetup('Nette\Application\Diagnostics\RoutingPanel::initializePanel');
        }

        $container->addDefinition($this->prefix('presenterFactory'))
            ->setClass('Nette\Application\PresenterFactory', array(
                isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
            ));


        // routing
        $router = $container->addDefinition('router')// no namespace for back compatibility
        ->setClass(Nette\Application\Routers\RouteList::class);

        foreach ($config['routing']['routes'] as $mask => $action) {
            $router->addSetup('$service[] = new Nette\Application\Routers\Route(?, ?);', array($mask, $action));
        }

        if (!$container->parameters['productionMode'] && $config['routing']['debugger']) {
            $application->addSetup('\Tracy\Debugger::getBar()->addPanel(?)', array(
                new Nette\DI\Statement('Nette\Application\Diagnostics\RoutingPanel')
            ));
        }
    }


    public function afterCompile(Nette\PhpGenerator\ClassType $class) {
        $initialize = $class->methods['initialize'];
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // debugger
        foreach (array('email', 'editor', 'browser', 'strictMode', 'maxLen', 'maxDepth') as $key) {
            if (isset($config['debugger'][$key])) {
                $initialize->addBody('\Tracy\Debugger::$? = ?;', array($key, $config['debugger'][$key]));
            }
        }

        if (!$container->parameters['productionMode']) {
            if ($config['container']['debugger']) {
                $config['debugger']['bar'][] = 'Nette\DI\Diagnostics\ContainerPanel';
            }

            foreach ((array)$config['debugger']['bar'] as $item) {
                $initialize->addBody($container->formatPhp(
                    '\Tracy\Debugger::getBar()->addPanel(?);',
                    Nette\DI\Compiler::filterArguments(array(is_string($item) ? new Nette\DI\Statement($item) : $item))
                ));
            }

            foreach ((array)$config['debugger']['blueScreen'] as $item) {
                $initialize->addBody($container->formatPhp(
                    '\Tracy\Debugger::$blueScreen->addPanel(?);',
                    Nette\DI\Compiler::filterArguments(array($item))
                ));
            }
        }

        if (!empty($container->parameters['tempDir'])) {
            $initialize->addBody($this->checkTempDir($container->expand('%tempDir%/cache')));
        }

        foreach ((array)$config['forms']['messages'] as $name => $text) {
            $initialize->addBody('Nette\Forms\Rules::$defaultMessages[Nette\Forms\Form::?] = ?;', array($name, $text));
        }

        if ($config['session']['autoStart'] === 'smart') {
            $initialize->addBody('$this->getService("session")->exists() && $this->getService("session")->start();');
        } elseif ($config['session']['autoStart']) {
            $initialize->addBody('$this->getService("session")->start();');
        }

        if (empty($config['xhtml'])) {
            $initialize->addBody('Nette\Utils\Html::$xhtml = ?;', array((bool)$config['xhtml']));
        }

        if (isset($config['security']['frames']) && $config['security']['frames'] !== TRUE) {
            $frames = $config['security']['frames'];
            if ($frames === FALSE) {
                $frames = 'DENY';
            } elseif (preg_match('#^https?:#', $frames)) {
                $frames = "ALLOW-FROM $frames";
            }
            $initialize->addBody('header(?);', array("X-Frame-Options: $frames"));
        }

        foreach ($container->findByTag('run') as $name => $on) {
            if ($on) {
                $initialize->addBody('$this->getService(?);', array($name));
            }
        }
    }


    private function checkTempDir($dir) {
        // checks whether directory is writable
        $uniq = uniqid('_', TRUE);
        if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
            throw new Nette\InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
        }

        // tests subdirectory mode
        $useDirs = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
        @unlink("$dir/$uniq/_");
        @rmdir("$dir/$uniq"); // @ - directory may not already exist

        return 'Nette\Caching\Storages\FileStorage::$useDirectories = ' . ($useDirs ? 'TRUE' : 'FALSE') . ";\n";
    }

}
