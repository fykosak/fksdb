<?php

namespace FksappModule;



/**
 * @package presenters
 *
 * @author Jan Kubalek
 *
*/
class SpecMenu {
#	use Stack;

	/**
	 * @return array
	*/
	static public function genereSpecMenu() {
		$tmp = array();

		array_push($tmp, array('action_type' => 'fyziklani online', 'presenter' => 'FyziklaniOnline'));

		return $tmp;
	}
}
