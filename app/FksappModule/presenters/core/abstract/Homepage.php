<?php

namespace Fksapp;
use \Nette\Caching\Cache;



/**
 * class Homepage abstract class 
 *
 * @package presenters\core\abstract
 *
 * @author jan Kubalek
 * @see	Auth
 * @see	BasePresenter
 * @see	\Nette\Utils\Neon
*/
abstract class Homepage extends Auth {
	use FormatMenu;
	use OptMenu;
	use ShardMenu;

	/**
	 * Standard Nette startup function
	 * @param  void
	 * @return void
	 * @see	parent::startup
	*/
	public function startup() {
		parent::startup();
		$this->createResultArray();
		$this->createRootArray();

		$this->setLocalResultArray($this->getResultArray());
		$this->setLocalRootArray($this->getRootArray());
	}

	/**
	 * Standard Nette factory for components
	 * @param  void
	 * @return ListOfActionsControl
	 * @see	ListOfActionsControl
	*/
	public function createComponentListOfActions() {
		$pull = $this->pullListOfActions();
		$list_of_actions = new BaseMenu('Akce', 'list_of_actions');

		$list_of_actions->setResultArray($pull);

		return $list_of_actions;
	}



	protected function checkParameters() {
	}

	/**
	 * 
	 * @param  void
	 * @return array Array in the ResultArray format
	*/
	protected function createResultArray() {
		$spec_menu         = SpecMenu::genereSpecMenu();
		$space_menu_length = count($spec_menu);
		$table             = $this->getService('ServiceEvent')->getTable();

		$presenter_action  = 'display';
		while($action = $table->fetch()) {
			$presenter    = 'awa';
			$name         = mb_strtolower($action->name, 'UTF-8');
			$first_letter = substr($name, 0, 1);
			$first_letter = mb_strtoupper($first_letter, 'UTF-8');
			$action_name  = $first_letter . substr($name, 1);

			$action_type_name = $action->getEventType()->name;
			foreach($spec_menu as $item) {
				if(strtolower($action_type_name) === strtolower($item['action_type'])) {
					$presenter = $item['presenter'];
					break;
				}
			}

			$this->addAction($name, $action_name, $presenter, $presenter_action, $action->event_id);
		}
	}

	/**
	 * 
	 * @return array Array in the rootArray format
	*/
	protected function createRootArray() {
		// TODO
	}

	/**
	 * Returns (compose) the list of actions!
	 * @param  void
	 * @return array
	 * @access protected
	*/
	protected function pullListOfActions() {
		$result_array = $this->getLocalResultArray();
		$length = count($result_array);

		$new_array = array();
		for($i = 0; $i < $length; $i++) {
			$new_array[$i]['presenter'] = $result_array[$i]['presenter'];
			$new_array[$i]['action']	= $result_array[$i]['action'];
			$new_array[$i]['display']   = $result_array[$i]['display'];
			$new_array[$i]['args'][0]   = $result_array[$i]['id'];
		}

		return $new_array;
	}

	/**
	 * Returns (compose) the rl!
	 * @param  void
	 * @return array
	 * @access protected
	*/
	protected function pullRootList() {
		// TODO
	}



	/**
	 * Sets the $local_result_array...
	 * @param  array Array in the ResultArray format
	 * @return OptMenu
	 * @access protected
	*/
	protected function setLocalResultArray($local_result_array) {
		if(DEBUG_MODE === 1) {
			FormatMenu::checkValidity($local_result_array);
		}

		$this->local_result_array = $local_result_array;

		return $this;
	}

	/**
	 * Sets the $local_root_array...
	 * @param  array Array in the RootArray format
	 * @return OptMenu
	 * @access protected
	*/
	protected function setLocalRootArray($local_root_array) {
		if(DEBUG_MODE === 1) {
			// TODO
			//FormatMenu::checkValidity($local_result_array);
		}

		$this->local_root_array = $local_root_array;

		return $this;
	}

	/**
	 * Gets the $local_result_array... 
	 * @return array Array in the ResultArray format
	 * @access public
	*/
	public function getLocalResultArray() {
		return $this->local_result_array;
	}

	/**
	 * Gets the $local_root_array... 
	 * @return array Array in the RootArray format
	 * @access public
	*/
	public function getLocalRootArray() {
		return $this->local_root_array;
	}



	/**
	 * @var    array
	 * @access private
	*/
	private $local_result_array;

	/**
	 * @var    array
	 * @access private
	*/
	private $local_root_array;
}
