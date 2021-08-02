<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Org;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

class OrderColumnFactory extends ColumnFactory
{

    /**
     * @param AbstractModel|ModelOrg $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        if (\array_key_exists($model->order, $this->getOrderMapping())) {
            return (new StringPrinter())($this->getOrderMapping()[$model->order]);
        }
        return (new StringPrinter())($model->order);
    }

    public function getOrderMapping(): array
    {
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
            9 => '9 - ' . _('Head of the seminar'),
        ];
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new SelectBox($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->setItems($this->getOrderMapping());
        $control->setPrompt(_('Select rank'));
        $control->addRule(Form::FILLED, _('Please select rank.'));
        return $control;
    }
}
