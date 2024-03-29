<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Organizer;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\UI\StringPrinter;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<OrganizerModel,never>
 */
class OrderColumnFactory extends ColumnFactory
{
    /**
     * @phpstan-return string[]
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
     * @param OrganizerModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\array_key_exists($model->order, $this->getOrderMapping())) {
            return StringPrinter::getHtml($this->getOrderMapping()[$model->order]);
        }
        return StringPrinter::getHtml((string)$model->order);
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
