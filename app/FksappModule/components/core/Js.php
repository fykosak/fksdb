<?php
/**
 * Base class for JsControl
 *
 * @package components\core
 *
 * @author  Jan Kubalek
 *
 * @see \Nette\Application\UI\Control
*/ 

namespace FksappModule;



class Js extends BaseControl {
	public function __construct() {
		Js::setJsFile('');
	}



	/**
	 * Sets the js_file...
	 * @param  string
	 * @return boolean (true - OK)
	 * @access protected
	 * @see    \HomepageModule::Js::$Js_path;
	*/
	protected function setJsFile($js_file) {
		$this->js_file = $js_file;

		return true;
	}

	/**
	 * Gets the js_file...
	 * @param  void
	 * @return string
	 * @access protected
	 * @see    \HomepageModule::Js::$Js_file
	*/
	public function getJsFile() {
		return $this->js_file;
	}



	/**
	 * Js file
	 * @var string
	 * @access private
	*/
	private $js_file;
}
