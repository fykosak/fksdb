<?php

namespace FksappModule;



/**
 *
 * @package presenters
 *
 * @author jan Kubalek
 * @see \HomepageModule\Homepage
*/ 
class HomepagePresenter extends Homepage {

	/**
	 * Standard Nette render function
	*/
	public function render() {
	}



	protected function pullActionsWActions() {
		return array();
	}
}
