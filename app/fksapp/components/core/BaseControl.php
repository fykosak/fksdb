<?php
/**
 * This component creates 
 *
 * @package components\core
 *
 * @author  Jan Kubalek
 *
 * @see \Nette\Application\UI\Control
*/ 

namespace Fksapp;



use Nette\Application\UI\Control;

class BaseControl extends Control {
	public function __construct() {
	}



	/**
	 * Sets tse_he url...
	 * @param  string
	 * @return boolean (true - OK)
	 * @access public
	*/
	public function setBaseUrl($base_url) {
		$this->base_url = $base_url;

		return true;
	}

	/**
	 * Gets the base_url...
	 * @param  void
	 * @return string
	 * @access public
	*/
	public function getBaseUrl() {
		return $this->base_url;
	}



	/**
	 * @var    string
	 * @access private
	*/
	private $base_url = '';
}
