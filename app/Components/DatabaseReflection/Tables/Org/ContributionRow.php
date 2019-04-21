<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class ContributionRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class ContributionRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Contribution');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
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
