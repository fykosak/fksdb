<?php

namespace Fksapp;
use \Nette\Application\Request;



/**
 *
 * @package presenters\core\abstract
 *
 * @author jan Kubalek
 *
 * @see	Homepage
 * @see	ActionInterface
*/ 
abstract class Action extends Homepage {

	/**
	 * Standard Nette factory for components
	 * @param  void
	 * @return ListOfActionsControl
	 * @see	ActionsWActionsControl
	*/
	public function createComponentActionsWActions() {
		$pull = $this->pullActionsWActions();

		$awa_component = new BaseMenu('Přihláška', 'actions_with_actions');
		$awa_component->setResultArray($pull);

		return $awa_component;
	}



	/**
	 * Checks presenter params...
	 *  ( checksParama overlaps his abstract brother 
	 *	which located in the parent class because class AAA
	 *	does not have to overlap this "abstract parent" )
	 * @param  void
	 * @return void
	 * @access public
	 * @see	BasePresenter
	*/
	public function checkParameters() {
		$event_table  = $this->getService('ServiceEvent')->getTable();
		$event_id     = $this->getParameter('id');
		$event_max_id = $event_table->max('event_id');
		$event        = NULL;

		if( ! ($event_id === NULL)) {
			if($event_id <= $event_max_id) {
				$row   = $event_table->where("event_id = ?", $event_id)->fetch();
				$event = $row;
			}
			else {
				throw new \Nette\OutOfRangeException('Action id is out of range!');
			}
		}
		else {
			$event = NULL;
		}

		$this->setModelEvent($event);

		return true;
	}


	/**
	 * Returns (compose) the actions with actions!
	 * @return List of actions with actions
	 * @access protected
	*/
	protected function pullActionsWActions() {
		$id     = $this->getEventId();
		$action = $this->findActionById($id);
		if(count($action) === 0) {
			throw new \Nette\ArgumentOutOfRangeException("Action::pullActionsWActions(): action with id $id doe not exist in the menu cache!");
		}

		$new_array = $action[0]['awa'];
		$length    = count($new_array);
		for($i = 0; $i < $length; $i++) {
			$new_array[$i]['args'][0] = $id;
		}

		return $new_array;
	}



	/**
	 * @param  ModelEvent
	 * @return Homepage
	 * @access protected
	*/
	protected function setModelEvent($model_event) {
		$this->model_event = $model_event;

		return $this;
	}

	/**
	 * @return ModelEvent
	 * @access public
	*/
	public function getModelEvent() {
		return $this->model_event;
	}

	/**
	 * @return integer
	 * @access public
	*/
	public function getEventId() {
		$model = $this->getModelEvent();
		if($model === NULL) {
			return NULL;
		}

		return $model->getPrimary();
	}

	public function getEventYear() {
		$model = $this->getModelEvent();
		if($model === NULL) {
			return NULL;
		}

		return $model->year;
	}



	/**
	 * @var    ModelEvent
	 * @access private
	*/
	private $model_event;
}
