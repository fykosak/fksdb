<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ReportRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class ReportRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Report');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('Shrnující text k akci.');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextArea(self::getTitle());
        $control->setOption('description', $this->getDescription());
        return $control;
    }
}
