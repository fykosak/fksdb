<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;

/**
 * Class CareerField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class CareerRow extends AbstractRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Co právě dělá');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('Zobrazeno v seznamu organizátorů');
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

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    protected function getModelAccessKey(): string {
        return 'career';
    }
}
