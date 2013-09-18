<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelEvent extends AbstractModelSingle {

	public function getEventType() {
		return $this->ref(DbNames::TAB_EVENT_TYPE, 'event_type_id');
	}
}

?>
