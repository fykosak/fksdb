<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ContributionRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class ContributionRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contribution');
    }

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'contribution';
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextArea($this->getTitle());
        $control->setOption('description', _('Zobrazeno v síni slávy'));
        return $control;
    }
}
