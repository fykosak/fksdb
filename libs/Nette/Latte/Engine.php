<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Latte
 */



/**
 * Templating engine Latte.
 *
 * @author     David Grudl
 * @package Nette\Latte
 */
class NLatteFilter extends NObject
{
	/** @var NParser */
	private $parser;

	/** @var NLatteCompiler */
	private $compiler;



	public function __construct()
	{
		$this->parser = new NParser;
		$this->compiler = new NLatteCompiler;
		$this->compiler->defaultContentType = NLatteCompiler::CONTENT_XHTML;

		NCoreMacros::install($this->compiler);
		$this->compiler->addMacro('cache', new NCacheMacro($this->compiler));
		NUIMacros::install($this->compiler);
		NFormMacros::install($this->compiler);
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		return $this->compiler->compile($this->parser->parse($s));
	}



	/**
	 * @return NParser
	 */
	public function getParser()
	{
		return $this->parser;
	}



	/**
	 * @return NLatteCompiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

}
