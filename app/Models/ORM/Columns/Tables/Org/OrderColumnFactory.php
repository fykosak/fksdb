<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Org;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<OrgModel,never>
 */
class OrderColumnFactory extends ColumnFactory
{
    /**
     * @return string[]
     */
    public function getOrderMapping(): array
    {
        return [
            0 => '0 - ' . _('Passive organizer'),
            1 => '1 - ' . _('Organizer'),
            2 => '2 - ' . _('Active organizer'),
            3 => '3 - ',
            4 => '4 - ' . _('Leadership'),
            5 => '5 - ',
            6 => '6 - ' . _('Deputy head organizer'),
            7 => '7 - ' . _('Head organizer'),
            8 => '8 - ',
            9 => '9 - ' . _('Head of the seminar'),
        ];
    }

    /**
     * @param OrgModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\array_key_exists($model->order, $this->getOrderMapping())) {
            return (new StringPrinter())($this->getOrderMapping()[$model->order]);
        }
        return (new StringPrinter())((string)$model->order);
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
