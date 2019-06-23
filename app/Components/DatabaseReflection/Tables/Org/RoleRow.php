<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class RoleRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class RoleRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Role');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = parent::createField();
        $control->addRule(Form::MAX_LENGTH, null, 255);
        return $control;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'role';
    }
}
