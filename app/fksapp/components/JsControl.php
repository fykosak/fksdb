<?php
/**
 * This component creates link for JS
 *
 * @author  Jan Kubalek
 *
 * @package components
 *
 * @see Js
*/ 

namespace Fksapp;



class JsControl extends Js {
	public function __construct() {
		parent::__construct();
	}



	/**
	 * Standard Nette render function
	 * @param  valid file
	 * @return string
	 * @access public
	*/
	public function render($file) {
		if( ! isset($file)) {
			throw new \Nette\InvalidArgumentException('JsControl::render - missing arg');
		}
		$this->setJsFile($file);

		$template = $this->template;
		$template->setFile(__DIR__ . '/templates/Js.latte');

		$template->js       = $this->getJsFile();
		$template->base_url = $this->getBaseUrl();

		$template->render();

		return true;
	}
}
