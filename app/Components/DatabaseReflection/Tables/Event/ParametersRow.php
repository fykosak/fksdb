<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ParametersRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class ParametersRow extends AbstractEventRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Parameters');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextArea($this->getTitle());
        $control->setOption('description', _('V Neon syntaxi, schéma je specifické pro definici akce.'));
        return $control;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'parameters';
    }
}
