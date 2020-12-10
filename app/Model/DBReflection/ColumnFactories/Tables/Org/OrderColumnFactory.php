<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Org;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ValuePrinters\StringPrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class OrderRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrderColumnFactory extends DefaultColumnFactory {

    public function getOrderMapping(): array {
        return [
            0 => '0 - ' . _('Passive organiser'),
            1 => '1 - ' . _('Organiser'),
            2 => '2 - ' . _('Active organiser'),
            3 => '3 - ',
            4 => '4 - ' . _('Leadership'),
            5 => '5 - ',
            6 => '6 - ' . _('Deputy head organiser'),
            7 => '7 - ' . _('Head organiser'),
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
