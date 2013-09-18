<?php

namespace Fksapp;
use \Nette\Application\UI\Presenter;
use \Nette\Application\UI\PresenterComponent;
use \Nette\Application\Request;



/**
 *
 * Each presenter in this app must inherits BasePresenter!
 *
 * @package presenters\core\abstract
 *
 * @author Jan Kubalek
 * @see    \Nette\Application\UI\Presenter
*/
abstract class BasePresenter extends Presenter implements BaseInterface {
	/**
	 * Standard constructor
	*/
	public function __construct(\Nette\Database\Connection $connection) {
		parent::__construct();

		$this->setConnection($connection);
	}



	/**
	 * Call the PresenterComponent::loadState($params) and checkParameters()
	 * @see \Nette\Application\UI\PresenterComponent::loadState(array $params)
	*/
	public function loadState(array $params) {
		PresenterComponent::loadState($params);
		$this->checkParameters();
	}

	/**
	 * Standard Nette startup function
	 * @param  void
	 * @return boolean (true - OK)
	 * @access protected
	*/
	protected function startup() {
		parent::startup();
		$this->invalidLinkMode = self::INVALID_LINK_EXCEPTION;
		$this->setConfigConstants();

		$backlink = $this->getSession('backlink');
		$this->setBacklink($backlink->backlink);
	}

	/**
	 * On the end of the presenter life cyclus
	 * - save the current request to the user session
	 * @param Nette\Application\IResponse
	 * @return void
	 * @access public
	*/
	public function shutdown($response) {
		#$backlink = $this->getSession('backlink');
		#$backlink->backlink = $this->storeRequest();
	}


	/**
	 * Standard Nette factory for components
	 * @param  void
	 * @return CssControl
	 * @access public
	*/
	public function createComponentCss() {
		$css = new CssControl();

		return $css;
	}

	/**
	 *
	*/
	abstract protected function checkParameters();

	/**
	 * @return void
	 * @access public
	*/
	public function saveBacklink($backlink = NULL) {
		if($backlink === NULL) {
			$backlink = $this->storeRequest();
		}
		$session = $this->getSession('backlink');
		$session->backlink = $backlink;
		$this->setBacklink($backlink);
	}

	public function removeBacklink() {
		$this->getSession('backlink')->remove();
		$this->setBacklink("");
	}



	/**
	 * @param \Nette\Database\Connection
	 * @return Homepage
	 * @access protected
	*/
	protected function setConnection(\Nette\Database\Connection $connection) {
		$this->connection = $connection;

		return $this;
	}

	/**
	 * Sets the standard config constants to the presenter template
	 * @param  void
	 * @return boolean (true - OK)
	 * @access private
	*/
	private function setConfigConstants() {
		$template = $this->template;

		$template->WWW_DIR = WWW_DIR;

		return true;
	}

	protected function setBacklink($backlink) {
		$this->backlink = $backlink;
	}

	/**
	 * @inheritdoc
	*/
	public function getPresenterName() {
		$pos = strrpos($this->name, ':');
		if (is_int($pos)) {
			return substr($this->name, $pos + 1);
		}

		return $this->name;
	}

	/**
	 * @param  void
	 * @return \Nette\Database\Connection
	 * @access public
	*/
	public function getConnection() {
		return $this->connection;
	}

	public function getBacklink() {
		return $this->backlink;
	}



	/**
	 * @var string String in the format Nette\Application\UI\Presenter::storeRequest()
	*/
	private $backlink;

	/**
	 * @var \Nette\Database\Connection
	*/
	private $connection;
}




