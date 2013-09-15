<?php


namespace Fksapp;

/**
 * Base class for CssControl
 *
 * @package components\core
 *
 * @author  Jan Kubalek
 *
*/ 
class Css extends BaseControl {
	public function __construct() {
		Css::setCssFile('');
	}



	/**
	 * Sets the css_file...
	 * @param  string
	 * @return boolean (true - OK)
	 * @access protected
	 * @see    \HomepageModule::Css::$css_path;
	*/
	protected function setCssFile($css_file) {
		$this->css_file = $css_file;

		return true;
	}

	/**
	 * Gets the css_file...
	 * @param  void
	 * @return string
	 * @access protected
	 * @see    \HomepageModule::Css::$css_file
	*/
	public function getCssFile() {
		return $this->css_file;
	}



	/**
	 * css file
	 * @var string
	 * @access private
	*/
	private $css_file;
}
