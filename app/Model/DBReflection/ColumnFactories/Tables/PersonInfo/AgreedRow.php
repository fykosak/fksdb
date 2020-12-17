<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\PersonInfo;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ValuePrinters\DatePrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

/**
 * Class AgreedField
 * *
 */
class AgreedRow extends DefaultColumnFactory {

    /**
     * @param array $args
     * @return BaseControl
     */
    protected function createFormControl(...$args): BaseControl {
        $control = new Checkbox($this->getTitle());
        $link = Html::el('a');
        $link->setText(_('Agreement'));
        $link->addAttributes([
            'href' => _('http://fykos.cz/doc/souhlas.pdf'),
            'target' => '_blank',
        ]);
        $control->setOption('description', $link);
        return $control;
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter())($model->agreed);
    }
}
