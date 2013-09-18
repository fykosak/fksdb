<?php
/**
 * This component creates link for CSS
 *
 * @author  Jan Kubalek
 *
 * @package components
 *
 * @see \HomepageModule\Css
*/ 

namespace FksappModule;



class CssControl extends Css {
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
			throw new \Nette\InvalidArgumentException('CssControl::render - missing arg');
		}
		$this->setCssFile($file);

		$template = $this->template;
		$template->setFile(__DIR__ . '/templates/Css.latte');

		$template->css      = $this->getCssFile();
		$template->base_url = $this->getBaseUrl();

		$template->render();

		return true;
	}
}
