<?php

namespace FksappModule;



/**
 * TODO check if the adding action has unique name
 *
 * @author Jan Kubalek
 *
 * @package presenters\core\traits
*/
trait OptMenu {
	/**
	 * Create new action
	 * @param  string
	 * @param  string
	 * @param  string/empty_string
	 * @param  array/NULL
	 * @return OptMenu
	 * @access protected
	*/
	public function AddAction($action_name, $action_display, $presenter = "", $action = "", $id = NULL, $active = 0, $awa = array()) {
		$test = $this->findActionByName($action_name);
		if( ! (count($test) === 0)) {
			throw new \Nette\ArgumentOutOfRangeException("Action with name '$action_name' exists!");
		}

		$result_array = &$this->getResultArray();
		$action_array = FormatMenu::formatAction($action_name, $presenter, $action, $id, $action_display, $awa, $active);

		array_push($result_array, $action_array);
		$this->setPrevAction(1);
		$this->setPrevRoot(0);
		$this->setPrevActionsWActions(0);

		return $this;
	}

	/**
	 * Adds the awa section to the corresponding action...
	 * @param  string
	 * @param  string
	 * @param  string/NULL (NULL due to neon compatibility)
	 * @return OptMenu
	 * @access public
	*/
	public function AddActionsWActions($action_name, $awa_display, $presenter = "", $action = "", $active = 0) {
		$action_array = &$this->findActionByName($action_name);
		if(count($action_array) === 0) {
			throw new \Nette\InvalidArgumentException("Action with name $action_name does not exist!");
		}

		$length = count($action_array);
		for($i = 0; $i < $length; $i++) {
			$awa  = &$action_array[$i]['awa'];
			$nawa = FormatMenu::formatActionsWActions($presenter, $action, $awa_display, $active);

			array_push($awa, $nawa);
		}

		$this->setPrevAction(0);
		$this->setPrevRoot(0);
		$this->setPrevActionsWActions(1);

		return $this;
	}

	/**
	 * Adds the awa section to the corresponding action...
	 * @param  string
	 * @param  string
	 * @param  string/NULL (NULL due to neon compatibility)
	 * @return OptMenu
	 * @access public
	*/
	public function AddRoot($root_name, $root_display, $action = '', $active = 0) {
		$root_array = &$this->getRootArray();
		$root       = FormatMenu::formatRoot($root_name, $action, $root_display);

		array_push($root_array, $root);
		$this->setPrevAction(0);
		$this->setPrevRoot(1);
		$this->setPrevActionsWActions(0);
		
		return $this;
	}

	/**
	 * Sets the correspond action section
	 * @param string
	 * @return OptMenu
	 * @access public
	*/
	public function setAction($action) {
		$prev_action = $this->getPrevAction();
		$prev_root   = $this->getPrevRoot();
		$prev_awa    = $this->getPrevActionsWActions();
		if((!$prev_action) && (!$prev_awa) && (!$prev_root)) {
			throw new \Nette\UnexpectedValueException("setAction($action) used to in invalid context");
		}

		$result_array = &$this->getResultArray();
		$length = count($result_array);
		if($prev_action) {
			$result_array[$length - 1]['action'] = $action;
			return $this;
		}

		$result_array = &$this->getResultArray();
		$length = count($result_array);
		if($prev_action) {
			$root_array[$length - 1]['action'] = $action;
			return $this;
		}

		if($prev_awa) {
			$awa = &$result_array[$length - 1]['awa'];
			$awa_length = count($awa);

			if($awa_length === 0) {
				array_push($awa, FormatMenu::formatActionsWActions('', $action, '', 1));
			}
			else {
				$awa[$awa_length - 1]['action'] = $action;
			}
		}

		return $this;
	}

	/**
	 * Sets the correspond 'active' section
	 * @param string
	 * @return OptMenu
	 * @access public
	*/
	public function setActive($act) {
		$prev_action = $this->getPrevAction();
		$prev_root   = $this->getPrevRoot();
		$prev_awa    = $this->getPrevActionsWActions();
		if((!$prev_action) && (!$prev_awa) && (!$prev_root)) {
			throw new \Nette\UnexpectedValueException("setActive($act) used to in invalid context");
		}

		$result_array = &$this->getResultArray();
		$length = count($result_array);
		if($prev_action) {
			$result_array[$length - 1]['active'] = $act;
			return $this;
		}

		$root_array = &$this->getRootArray();
		$length = count($root_array);
		if($prev_root) {
			$root_array[$length - 1]['active'] = $act;
			return $this;
		}

		if($prev_awa) {
			$awa = &$result_array[$length - 1]['awa'];
			$awa_length = count($awa);

			if($awa_length === 0) {
				array_push($awa, FormatMenu::formatActionsWActions('', '', '', $act));
			}
			else {
				$awa[$awa_length - 1]['active'] = $act;
			}

			return $this;
		}
	}	

	/**
	 * Sets the correspond 'presenter' section
	 * @param string
	 * @return OptMenu
	 * @access public
	*/
	public function setPresenter($presenter) {
		$prev_action = $this->getPrevAction();
		$prev_root   = $this->getPrevRoot();
		$prev_awa    = $this->getPrevActionsWActions();
		if((!$prev_action) && (!$prev_awa) && (!$prev_root)) {
			throw new \Nette\UnexpectedValueException("setPresenter($presenter) used to in invalid context");
		}

		$result_array = &$this->getResultArray();
		$length = count($result_array);
		if($prev_action) {
			$result_array[$length - 1]['presenter'] = $presenter;
			return $this;
		}

		$root_array = &$this->getRootArray();
		$length = count($root_array);
		if($prev_root) {
			$root_array[$length - 1]['presenter'] = $presenter;
			return $this;
		}

		if($prev_awa) {
			$awa = &$result_array[$length - 1]['awa'];
			$awa_length = count($awa);

			if($awa_length === 0) {
				array_push($awa, FormatMenu::formatActionsWActions($presenter, '', '', 1));
			}
			else {
				$awa[$awa_length - 1]['presenter'] = $presenter;
			}

			return $this;
		}
	}	

	/**
	 * Sets the awa section
	 * @param array
	 * @return OptMenu
	 * @access public
	*/
	public function setActionsWActions($awa) {
		if( ! $this->getPrevAction()) {
			throw new \Nette\UnexpectedValueException("setActionActionsWActions($accessible) used to in invalid context");
		}

		$result_array = &$this->getResultArray();
		$length = count($result_array);

		$result_array[$length - 1]['awa'] = $awa;

		return $this;
	}



	/**
	 * Creates the list of actions 
	 * @param void
	 * @return array (0 => <first_added_action>, 1 => <second_added_action>, ...)
	 * @access public
	*/
	public function createListOfActions() {
		$result_array = &$this->getResultArray();
		$length       = count($result_array);

		$list_of_actions = array();
		for($i = 0; $i < $length; $i++) {
			array_push($list_of_actions, $result_array[$i]['action_name']);
		}

		return $list_of_actions;
	}

	/**
	 * Finds actions by name and then returns reference/copy on this action...
	 * @param  string action_name
	 * @return array
	 * @access public
	*/
	public function &findActionByName($action_name) {
		$result_array = &$this->getResultArray();
		$length       = sizeof($result_array);

		$action_stack = array();
		$k = 0;
		for($i = 0; $i < $length; $i++) {
			if($result_array[$i]['action_name'] === $action_name) {
				$action_stack[$k] = &$result_array[$i];
				$k++;
			}
		}

		return $action_stack;
	}

	/**
	 * Finds actions by name and then returns reference/copy on this action...
	 * @param  string action_name
	 * @return array
	 * @access public
	*/
	public function &findActionById($id) {
		$result_array = &$this->getResultArray();
		$length       = count($result_array);

		$action_stack = array();
		$k = 0;
		for($i = 0; $i < $length; $i++) {
			if($result_array[$i]['id'] === $id) {
				$action_stack[$k] = &$result_array[$i];
				$k++;
			}
		}

		return $action_stack;
	}

	/**
	 * Finds root_actions by name and then returns reference/copy on this...
	 * @param  string root_name
	 * @return array
	 * @access public
	*/
	public function &findRootByName($root_name) {
		$root_array = &$this->getRootArray();
		$length     = count($root_array);

		$root_stack = array();
		$k = 0;
		for($i = 0; $i < $length; $i++) {
			if($root_array[$i]['root_name'] === $root_name) {
				$root_stack[$k]=  &$root_array[$i]['root_name'];
				$k++;
			}
		}

		return $root_stack;
	}



	/**
	 * Sets the $result_array...
	 * @param  array Array in the ResultArray format
	 * @return OptMenu
	 * @access protected
	*/
	protected function setResultArray($result_array) {
		if(DEBUG_MODE === 1) {
			// TODO
			// FormatMenu::checkValidity($result_array);
		}

		$this->result_array = $result_array;
		$this->setPrevAction(0);
		$this->setPrevRoot(0);
		$this->setPrevActionsWActions(0);

		return $this;
	}

	/**
	 * Sets the $root_array...
	 * @param  array Array in the RootArray format
	 * @return OptMenu
	 * @access protected
	*/
	protected function setRootArray($root_array) {
		if(DEBUG_MODE === 1) {
			// TODO
		}

		$this->root_array = $root_array;
		$this->setPrevAction(0);
		$this->setPrevRoot(0);
		$this->setPrevActionsWActions(0);

		return $this;
	}

	/**
	 * Sets the $prev_action...
	 * @param  boolean
	 * @return void
	 * @access private
	*/
	private function setPrevAction($prev_action) {
		$this->prev_action = $prev_action;
	}

	/**
	 * Sets the $prev_root...
	 * @param  boolean
	 * @return void
	 * @access private
	*/
	private function setPrevRoot($prev_root) {
		$this->prev_root = $prev_root;
	}

	/**
	 * Sets the $prev_actions_w_actions...
	 * @param  boolean
	 * @return void
	 * @access private
	*/
	private function setPrevActionsWActions($prev_awa) {
		$this->prev_actions_w_actions = $prev_awa;
	}

	/**
	 * Gets the $result_array... (as copy or as reference)
	 * @return array Array in the ResultArray format (copy or reference)
	 * @access public
	*/
	public function &getResultArray() {
		return $this->result_array;
	}

	/**
	 * Gets the $root_array... (as copy or as reference)
	 * @return array Array in the RootArray format (copy or reference)
	 * @access public
	*/
	public function &getRootArray() {
		return $this->root_array;
	}

	/**
	 * Gets the $prev_action... (as copy or as reference)
	 * @return boolean
	 * @access private
	*/
	private function &getPrevAction() {
		return $this->prev_action;
	}

	/**
	 * Gets the $prev_root... (as copy or as reference)
	 * @return boolean
	 * @access private
	*/
	private function &getPrevRoot() {
		$this->prev_root;
	}

	/**
	 * Gets the $result_array... (as copy or as reference)
	 * @return boolean
	 * @access private
	*/
	private function &getPrevActionsWActions() {
		return $this->prev_actions_w_actions;
	}



	/**
	 * @var array
	 * @access private
	 * [ORDER]:
	 *         action_name: [value]
	 *         action:      [value]
	 *         display:     [value]
	 *         awa:
	 *             [ORDER]:
	 *                 action:    [value]
	 *                 display:   [value]
	 *              .
	 *              .
	 *              .
	*/
	private $result_array = array();

	/**
	 * @var array
	 * @access private
	 * [ORDER]:
	 *         root_name: [value]
	 *         presenter: [value]
	 *         action:    [value]
	 *         display:   [value]
	 *    .
	 *    .
	 *    .
	*/
	private $root_array = array();

	/**
	 * @var boolean
	 * @access private
	*/
	private $prev_action = 0;

	/**
	 * @var boolean
	 * @access private
	*/
	private $prev_root = 0;

	/**
	 * @var boolean
	 * @access private
	*/
	private $prev_actions_w_actions = 0;
}
