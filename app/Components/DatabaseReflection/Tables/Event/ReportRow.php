<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ReportRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class ReportRow extends AbstractEventRowFactory {
    use DefaultPrinterTrait;

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
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextArea($this->getTitle());
        $control->setOption('description', $this->getDescription());
        return $control;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'report';
    }
}
