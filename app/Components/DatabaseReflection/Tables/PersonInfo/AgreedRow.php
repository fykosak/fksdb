<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

/**
 * Class AgreedField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AgreedRow extends AbstractRow {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Souhlasím se zpracováním osobních údajů');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new Checkbox($this->getTitle());
        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->addAttributes(['href' => _('http://fykos.cz/doc/souhlas.pdf')]);
        $control->setOption('description', $link);
        return $control;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

}
