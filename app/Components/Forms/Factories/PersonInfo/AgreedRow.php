<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Factories\AbstractRow;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\IControl;
use Nette\Utils\Html;

/**
 * Class AgreedField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AgreedRow extends AbstractRow {

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Souhlasím se zpracováním osobních údajů');
    }

    /**
     * @return IControl
     */
    public function createField(): IControl {
        $control = new Checkbox($this->getTitle());
        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->addAttributes(['href' => _("http://fykos.cz/doc/souhlas.pdf")]);
        $control->setOption('description', $link);
        return $control;
    }
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return 128;
    }

}
