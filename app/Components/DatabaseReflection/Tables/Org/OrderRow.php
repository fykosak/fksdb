<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

/**
 * Class OrderRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class OrderRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Order');
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
        $control = new SelectBox($this->getTitle());
        $control->setOption('description', _('Pro řazení v seznamu organizátorů'));
        $control->setItems([
            0 => '0 - org',
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4 - hlavní organizátor',
            9 => '9 - vedoucí semináře',
        ]);
        $control->setPrompt(_('Zvolit hodnost'));
        $control->addRule(Form::FILLED, _('Vyberte hodnost.'));
        return $control;
    }
}
