<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

/**
 * Class AgreedField
 * *
 */
class AgreedRow extends AbstractRow {

    public function getTitle(): string {
        return _('Souhlasím se zpracováním osobních údajů');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new Checkbox($this->getTitle());
        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->addAttributes(['href' => _('http://fykos.cz/doc/souhlas.pdf'),
                              'target' => '_blank']);
        $control->setOption('description', $link);
        return $control;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter())($model->agreed);
    }

}
