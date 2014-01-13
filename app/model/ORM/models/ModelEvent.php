<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelEvent extends AbstractModelSingle implements IResource {

    public function getEventType() {
        return ModelEventType::createFromTableRow($this->ref(DbNames::TAB_EVENT_TYPE, 'event_type_id'));
    }

    public function getResourceId() {
        return 'event';
    }

}

?>
