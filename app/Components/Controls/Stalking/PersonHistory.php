<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\DbNames;

/**
 * Class PersonHistory
 * @package FKSDB\Components\Controls\Stalking
 */
class PersonHistory extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->historys = $this->modelPerson->related(DbNames::TAB_PERSON_HISTORY, 'person_id');
        $this->template->setFile(__DIR__ . '/PersonHistory.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Person history');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_FULL, self::PERMISSION_RESTRICT];
    }
}
