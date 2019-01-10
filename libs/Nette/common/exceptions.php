<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette;

use Nette;



/**
 * The exception that indicates errors that can not be recovered from. Execution of
 * the script should be halted.
 */

class FatalErrorException extends \ErrorException
{

	public function __construct($message, $code, $severity, $file, $line, $context, \Exception $previous = NULL)
	{
		parent::__construct($message, $code, $severity, $file, $line, $previous);
		$this->context = $context;
	}

}


