<?php

namespace Fksapp;



/**
 * Stack for Menu "shards."
 *
 * @package presenters\core\traits
 *
 * @author Jan Kubalek
 *
*/
trait ShardMenu {

	/**
	 * @return integer last used index
	 * @param  array actionswactions
	 * @access public
	*/
	public function addShardAction($action) {
		if($DEBUG_MODE === 1) {
			FormatMenu::checkValidity_ACTION($action);
		}

		$shard_action = &$this->getShardAction();
		array_push($shard_action, $action);

		return (count($shard_action) - 1);
	}

	/**
	 * @return integer last used index
	 * @param  array actionswactions
	 * @access public
	*/
	public function addShardActionsWActions($awa) {
		if($DEBUG_MODE === 1) {
			FormatMenu::checkValidity_ACTIONS_WITH_ACTIONS($awa);
		}

		$shard_awa = &$this->getShardActionsWActions();
		array_push($shard_awa, $awa);

		return (count($shard_awa) - 1);
	}

	public function addShardRoot($awa) {
		throw new \Nette\InvalidArgumentException('Toto se nemelo stat');
		if($DEBUG_MODE === 1) {
			// TODO
			//FormatMenu::checkValidity_ACTIONS_WITH_ACTIONS($awa);
		}

		$shard_awa = &$this->getShardActionsWActions();
		array_push($shard_awa, $awa);

		return (count($shard_awa) - 1);
	}



	/**
	 * @return array
	 * @param  array ActionSWActions
	 * @access protected
	 * @see    FormatMenu
	*/
	protected function setShardAction($action) {
		if(DEBUG_MODE === 1) {
			// TODO
		}

		$this->shardAction = $action;

		return $this;
	}

	/**
	 * @return array
	 * @param  array ActionSWActions
	 * @access protected
	 * @see    FormatMenu
	*/
	protected function setShardActionsWActions($awa) {
		if(DEBUG_MODE === 1) {
			// TODO
		}

		$this->ShardActionsWActions = $awa;

		return $this;
	}

	/**
	 * @return array
	 * @access public
	 * @see    FormatMenu
	*/
	public function &getShardAction() {
		return $this->shardAction;
	}

	/**
	 * @return array
	 * @access public
	 * @see    FormatMenu
	*/
	public function &getShardActionsWActions() {
		return $this->ShardActionsWActions;
	}



	/**
	 * @var array
	*/
	private $shardAction = array();

	/**
	 * @var array
	*/
	private $shardActionsWActions = array();
}
