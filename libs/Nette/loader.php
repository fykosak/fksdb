<?php

/**
 * Nette Framework (version 2.0.12 released on 2013-08-08, http://nette.org)
 *
 * Copyright (c) 2004, 2013 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


/**
 * Check and reset PHP configuration.
 */
error_reporting(E_ALL | E_STRICT);
@iconv_set_encoding('internal_encoding', 'UTF-8'); // @ - deprecated since PHP 5.6
extension_loaded('mbstring') && mb_internal_encoding('UTF-8');
umask(0);
@header('X-Powered-By: Nette Framework'); // @ - headers may be sent
@header('Content-Type: text/html; charset=utf-8'); // @ - headers may be sent


/**
 * Load and configure Nette Framework.
 */
define('NETTE', TRUE);
define('NETTE_DIR', __DIR__);
define('NETTE_VERSION_ID', 20012); // v2.0.12
define('NETTE_PACKAGE', '5.3');

require_once __DIR__ . '/common/exceptions.php';
require_once __DIR__ . '/Utils/LimitedScope.php';

Nette\Utils\SafeStream::register();
