<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class EvenParticipantIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class EventParticipantIdRow extends AbstractParticipantRow {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event participant Id');
    }


    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->event_participant_id);
    }

}
