<?php

namespace FKSDB\DBReflection\ColumnFactories\Org;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class OrderRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrderRow extends DefaultColumnFactory {

    public function getOrderMapping(): array {
        return [
            0 => '0 - ' . _('pasivní org'),
            1 => '1 - ' . _('org'),
            2 => '2 - ' . _('aktivní org'),
            3 => '3 - ',
            4 => '4 - ' . _('vedení'),
            5 => '5 - ',
            6 => '6 - ' . _('zástupce hlavního organizátora'),
            7 => '7 - ' . _('hlavní organizátor'),
            8 => '8 - ',
            9 => '9 - ' . _('vedoucí semináře'),
        ];
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (\array_key_exists($model->order, $this->getOrderMapping())) {
            return (new StringPrinter())($this->getOrderMapping()[$model->order]);
        }
        return (new StringPrinter())($model->order);
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->setItems($this->getOrderMapping());
        $control->setPrompt(_('Select rank'));
        $control->addRule(Form::FILLED, _('Please select rank.'));
        return $control;
    }
}
