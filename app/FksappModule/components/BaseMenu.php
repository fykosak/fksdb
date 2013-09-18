<?php
/**
 * Base class for BaseMenu
 *
 * @package components\core
 *
 * @author Jan Kubalek
 *
 * @see \Nette\Application\UI\Control
*/ 

namespace Fksapp;



class BaseMenu extends \Nette\Application\UI\Control {
	public function __construct($header, $menu_id) {
		parent::__construct();
		$this->header  = $header;
		$this->menu_id = $menu_id;
	}



	/**
	 * Standard Nette render function
	 * @return void
	*/
	public function render() {
		$template = $this->template;
		$template->setFile(__DIR__ . '/templates/BaseMenu.latte');

		$template->result  = $this->getResultArray();
		$template->header  = $this->header;
		$template->menu_id = $this->menu_id;

		$template->render();
	}



	/**
	 * Sets the hash array for template...
	 * @param  array
	 * @return void
	*/
	public function setResultArray($result_array) {
		$this->result_array = $result_array;

		return $this;
	}

	/**
	 * Gets the $resut_array
	 * @return array
	*/
	protected function getResultArray() {
		return $this->result_array;
	}



	/**
	 * @see OptMenu::$root_array
		 * @var array
		 * array(
		 *    array(
		 *       0 => array (
		 *	        'presenter' => ,
		 *	        'action'    => ,
		 *	        'display'   => ,
		 *	        'args'      => array(...)    // NULL
		 *	         ),
		 *       1 => array (
		 *           ),
		 *	     .
		 *	     .
		 *       .
		 *    )
		 * );
	*/
	private $result_array = array();

	/**
	 * @var string
	*/
	private $header = '';

	/**
	 * @var string
	*/
	private $menu_id = '';
}
