<?php

namespace FksappModule;



/**
 * Support for applications.neon format
 * result_array format:
	 * [ORDER]:
	 *     action_name: [value]  string
	 *     presenter:   [value]  string
	 *     action:      [value]  string
	 *     display:     [value]  string
	 *     active:      [value]  boolean (0,1)
	 *     id:          [value]  integer
	 *     awa:
	 *         [ORDER]:
	 *             presenter: [value]  string
	 *             action:    [value]  string
	 *             display:   [value]  string
	 *             active:    [value]  boolean (0, 1)
	 *          .
	 *          .
	 *          .
 *
 * root_array format:
	 * [ORDER]:
	 *     root_name: [value]
	 *     presenter: [value]
	 *     action:    [value]
	 *     display:   [value]
	 *     active:    [value]
 *
 * @package presenters\core\traits
 *
 * @author Jan Kubalek
*/
trait FormatMenu {

	/**
	* @param  string action
	* @param  string display
	* @return array
	* @access public
	*/
	public static function formatActionsWActions($presenter, $action, $display, $active) {
		return array(
			'presenter' => "$presenter",
			'action'    => "$action",
			'display'   => "$display",
			'active'    => $active
			);
	}

	/**
	* @param  string   action name
	* @param  string   action
	* @param  string   display
	* @param  array    actions with actions
	* @return array
	* @access public
	*/
	public static function formatAction($action_name, $presenter, $action, $id, $display, $awa, $active) {
		return array(
			'action_name' => "$action_name",
			'presenter'   => "$presenter",
			'action'      => "$action",
			'display'     => "$display",
			'awa'         => $awa,
			'active'      => $active,
			'id'          => $id
			);
	}

	public static function formatRoot($root_name, $presenter, $action, $display, $active) {
		return array (
			'root_name' => $root_name,
			'presenter' => $presenter,
			'action'    => $action,
			'display'   => $display,
			'active'    => $active
			);
	}

	/**
	* @param  void
	* @return array
	* @access public static
	*/
	public static function formatActionsWActionsIndexes() {
		return array(
			0 => 'action',
			1 => 'display',
			2 => 'active',
			3 => 'presenter'
			);
	}

	/**
	* @param  void
	* @return array
	* @access public static
	*/
	public static function formatActionIndexes() {
		return array(
			0 => 'action_name',
			1 => 'action',
			2 => 'display',
			3 => 'awa',
			4 => 'active',
			5 => 'presenter',
			6 => 'id'
			);
	}



	/**
	* Check if $result_array is in the valid format
	* @param  void
	* @return boolean (true - OK)
	* @access public
	* @see    Homepage::$result_array
	*/
	static public function checkValidity($result_array) {
		if($result_array === NULL) {
			throw new \Nette\InvalidArgumentException('Homepage::checkValidity(arg): - $result_array === NULL');
		}

		$length = count($result_array);
		if($length === 0) {
			throw new \Nette\InvalidArgumentException('Homepage::checkValidity(arg): - count(arg) === 0');
		}

		$return_value = false;
		for($ORDER = 0; $ORDER < $length; $ORDER++) {
			if( ! isset($result_array[$ORDER])) {
				throw new \Nette\InvalidArgumentException("Homepage::checkValidity(arg): isset(arg[$ORDER]) == false");
			}

			$ACTION = $result_array[$ORDER];
			$return_value =  FormatMenu::checkValidity_ACTION($ACTION);
		}

		return $return_value;
	}

	/**
	 *
	 * @return boolean (true - OK)
	 * @access public
	 * @see    Homepage::$result_array/[ACTION]
	*/
	static public function checkValidity_ACTION($ACTION) {
		$length = count($ACTION);
		if( ! is_array($ACTION)) {
			throw new \Nette\InvalidArgumentException('Homepage::checkValidity_ACTION(arg): is_array(arg) == false');
		}
		if($length === 0) {
			throw new \Nette\InvalidArgumentException('Homepage::checkValidity_ACTION(arg): count(arg) == 0');
		}
		#if($length > 1) {
		#    throw new \Nette\InvalidArgumentException('Homepage::checkValidity_ACTION(arg): count(arg) > 1');
		#}

		$valid_keys = FormatMenu::formatActionIndexes();

		if( ! \Local\Checks::valid_keys($ACTION, $valid_keys)) {
			throw new \Nette\InvalidArgumentException('Homepage::checkValidity_ACTION(arg): invalid keys!');
		}

		return FormatMenu::checkValidity_ACTIONS_WITH_ACTIONS($ACTION['awa']);
	}

	/**
	 *
	 * @return boolean (true - OK)
	 * @access public
	 * @see    Homepage::$result_array/awa
	*/
	static public function checkValidity_ACTIONS_WITH_ACTIONS($awa) {
		if( !($awa === NULL) && !(count($awa) === 0)) {
			$length = count($awa);

			$valid_keys = FormatMenu::formatActionsWActionsIndexes();

			for($ORDER = 0; $ORDER < $length; $ORDER++) {
				if( ! \Local\Checks::valid_keys($awa[$ORDER], $valid_keys)) {
					throw new \Nette\InvalidArgumentException('Homepage::checkValidity_ACTION_WITH_ACTIONS(arg): invalid keys!');
				}
			}
		}

		return true;
	}
}
