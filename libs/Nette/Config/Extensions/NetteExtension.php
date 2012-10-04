<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Config\Extensions
 */



/**
 * Core Nette Framework services.
 *
 * @author     David Grudl
 * @package Nette\Config\Extensions
 */
class NNetteExtension extends NConfigCompilerExtension
{
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

	public $databaseDefaults = array(
		'dsn' => NULL,
		'user' => NULL,
		'password' => NULL,
		'options' => NULL,
		'debugger' => TRUE,
		'explain' => TRUE,
		'reflection' => 'NDiscoveredReflection',
	);



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);


		// cache
		$container->addDefinition($this->prefix('cacheJournal'))
			->setClass('NFileJournal', array('%tempDir%'));

		$container->addDefinition('cacheStorage') // no namespace for back compatibility
			->setClass('NFileStorage', array('%tempDir%/cache'));

		$container->addDefinition($this->prefix('templateCacheStorage'))
			->setClass('NPhpFileStorage', array('%tempDir%/cache'))
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('cache'))
			->setClass('NCache', array(1 => '%namespace%'))
			->setParameters(array('namespace' => NULL));


		// http
		$container->addDefinition($this->prefix('httpRequestFactory'))
			->setClass('NHttpRequestFactory')
			->addSetup('setEncoding', array('UTF-8'))
			->setInternal(TRUE);

		$container->addDefinition('httpRequest') // no namespace for back compatibility
			->setClass('NHttpRequest')
			->setFactory('@\NHttpRequestFactory::createHttpRequest');

		$container->addDefinition('httpResponse') // no namespace for back compatibility
			->setClass('NHttpResponse');

		$container->addDefinition($this->prefix('httpContext'))
			->setClass('NHttpContext');


		// session
		$session = $container->addDefinition('session') // no namespace for back compatibility
			->setClass('NSession');

		if (isset($config['session']['expiration'])) {
			$session->addSetup('setExpiration', array($config['session']['expiration']));
		}
		if (isset($config['session']['iAmUsingBadHost'])) {
			$session->addSetup('NFramework::$iAmUsingBadHost = ?;', array((bool) $config['session']['iAmUsingBadHost']));
		}
		unset($config['session']['expiration'], $config['session']['autoStart'], $config['session']['iAmUsingBadHost']);
		if (!empty($config['session'])) {
			$session->addSetup('setOptions', array($config['session']));
		}


		// security
		$container->addDefinition($this->prefix('userStorage'))
			->setClass('NUserStorage');

		$user = $container->addDefinition('user') // no namespace for back compatibility
			->setClass('NUser');

		if (!$container->parameters['productionMode'] && $config['security']['debugger']) {
			$user->addSetup('NDebugger::$bar->addPanel(?)', array(
				new NDIStatement('NUserPanel')
			));
		}

		if ($config['security']['users']) {
			$container->addDefinition($this->prefix('authenticator'))
				->setClass('NSimpleAuthenticator', array($config['security']['users']));
		}

		if ($config['security']['roles'] || $config['security']['resources']) {
			$authorizator = $container->addDefinition($this->prefix('authorizator'))
				->setClass('NPermission');
			foreach ($config['security']['roles'] as $role => $parents) {
				$authorizator->addSetup('addRole', array($role, $parents));
			}
			foreach ($config['security']['resources'] as $resource => $parents) {
				$authorizator->addSetup('addResource', array($resource, $parents));
			}
		}


		// application
		$application = $container->addDefinition('application') // no namespace for back compatibility
			->setClass('NApplication')
			->addSetup('$catchExceptions', $config['application']['catchExceptions'])
			->addSetup('$errorPresenter', $config['application']['errorPresenter']);

		if ($config['application']['debugger']) {
			$application->addSetup('NRoutingDebugger::initializePanel');
		}

		$container->addDefinition($this->prefix('presenterFactory'))
			->setClass('NPresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));


		// routing
		$router = $container->addDefinition('router') // no namespace for back compatibility
			->setClass('NRouteList');

		foreach ($config['routing']['routes'] as $mask => $action) {
			$router->addSetup('$service[] = new NRoute(?, ?);', array($mask, $action));
		}

		if (!$container->parameters['productionMode'] && $config['routing']['debugger']) {
			$application->addSetup('NDebugger::$bar->addPanel(?)', array(
				new NDIStatement('NRoutingDebugger')
			));
		}


		// mailer
		if (empty($config['mailer']['smtp'])) {
			$container->addDefinition($this->prefix('mailer'))
				->setClass('NSendmailMailer');
		} else {
			$container->addDefinition($this->prefix('mailer'))
				->setClass('NSmtpMailer', array($config['mailer']));
		}

		$container->addDefinition($this->prefix('mail'))
			->setClass('NMail')
			->addSetup('setMailer')
			->setShared(FALSE);


		// forms
		$container->addDefinition($this->prefix('basicForm'))
			->setClass('NForm')
			->setShared(FALSE);


		// templating
		$latte = $container->addDefinition($this->prefix('latte'))
			->setClass('NLatteFilter')
			->setShared(FALSE);

		if (empty($config['xhtml'])) {
			$latte->addSetup('$service->getCompiler()->defaultContentType = ?', NLatteCompiler::CONTENT_HTML);
		}

		$container->addDefinition($this->prefix('template'))
			->setClass('NFileTemplate')
			->addSetup('registerFilter', array($latte))
			->addSetup('registerHelperLoader', array('NTemplateHelpers::loader'))
			->setShared(FALSE);


		// database
		$container->addDefinition($this->prefix('database'))
				->setClass('NDINestedAccessor', array('@container', $this->prefix('database')));

		if (isset($config['database']['dsn'])) {
			$config['database'] = array('default' => $config['database']);
		}

		$autowired = TRUE;
		foreach ((array) $config['database'] as $name => $info) {
			if (!is_array($info)) {
				continue;
			}
			$info += $this->databaseDefaults + array('autowired' => $autowired);
			$autowired = FALSE;

			foreach ((array) $info['options'] as $key => $value) {
				unset($info['options'][$key]);
				$info['options'][constant($key)] = $value;
			}

			$connection = $container->addDefinition($this->prefix("database.$name"))
				->setClass('NConnection', array($info['dsn'], $info['user'], $info['password'], $info['options']))
				->setAutowired($info['autowired'])
				->addSetup('setCacheStorage')
				->addSetup('NDebugger::$blueScreen->addPanel(?)', array(
					'NDatabasePanel::renderException'
				));

			if ($info['reflection']) {
				$connection->addSetup('setDatabaseReflection', is_string($info['reflection'])
					? array(new NDIStatement(preg_match('#^[a-z]+$#', $info['reflection']) ? 'Nette\Database\Reflection\\' . ucfirst($info['reflection']) . 'Reflection' : $info['reflection']))
					: NConfigCompiler::filterArguments(array($info['reflection']))
				);
			}

			if (!$container->parameters['productionMode'] && $info['debugger']) {
				$panel = $container->addDefinition($this->prefix("database.{$name}ConnectionPanel"))
					->setClass('NDatabasePanel')
					->setAutowired(FALSE)
					->addSetup('$explain', !empty($info['explain']))
					->addSetup('NDebugger::$bar->addPanel(?)', array('@self'));

				$connection->addSetup('$service->onQuery[] = ?', array(array($panel, 'logQuery')));
			}
		}
	}



	public function afterCompile(NPhpClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// debugger
		foreach (array('email', 'editor', 'browser', 'strictMode', 'maxLen', 'maxDepth') as $key) {
			if (isset($config['debugger'][$key])) {
				$initialize->addBody('NDebugger::$? = ?;', array($key, $config['debugger'][$key]));
			}
		}

		if (!$container->parameters['productionMode']) {
			if ($config['container']['debugger']) {
				$config['debugger']['bar'][] = 'NContainerPanel';
			}

			foreach ((array) $config['debugger']['bar'] as $item) {
				$initialize->addBody($container->formatPhp(
					'NDebugger::$bar->addPanel(?);',
					NConfigCompiler::filterArguments(array(is_string($item) ? new NDIStatement($item) : $item))
				));
			}

			foreach ((array) $config['debugger']['blueScreen'] as $item) {
				$initialize->addBody($container->formatPhp(
					'NDebugger::$blueScreen->addPanel(?);',
					NConfigCompiler::filterArguments(array($item))
				));
			}
		}

		if (!empty($container->parameters['tempDir'])) {
			$initialize->addBody($this->checkTempDir($container->expand('%tempDir%/cache')));
		}

		foreach ((array) $config['forms']['messages'] as $name => $text) {
			$initialize->addBody('NRules::$defaultMessages[NForm::?] = ?;', array($name, $text));
		}

		if ($config['session']['autoStart'] === 'smart') {
			$initialize->addBody('$this->session->exists() && $this->session->start();');
		} elseif ($config['session']['autoStart']) {
			$initialize->addBody('$this->session->start();');
		}

		if (empty($config['xhtml'])) {
			$initialize->addBody('NHtml::$xhtml = ?;', array((bool) $config['xhtml']));
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



	private function checkTempDir($dir)
	{
		// checks whether directory is writable
		$uniq = uniqid('_', TRUE);
		if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
			throw new InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
		}

		// tests subdirectory mode
		$useDirs = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
		@unlink("$dir/$uniq/_");
		@rmdir("$dir/$uniq"); // @ - directory may not already exist

		return 'NFileStorage::$useDirectories = ' . ($useDirs ? 'TRUE' : 'FALSE') . ";\n";
	}

}
