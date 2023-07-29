<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RelatedTable;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\FilterList;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

class PaymentList extends FilterList
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, 1024);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PaymentModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->event->getPayments();
        foreach ($this->filterParams as $key => $param) {
            if (!$param) {
                continue;
            }
            switch ($key) {
                case 'state':
                    $query->where('state', $param);
                    break;
                case 'vs':
                    $query->where('variable_symbol', $param);
            }
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $states = [];
        foreach (PaymentState::cases() as $case) {
            $states[$case->value] = $case->label();
        }
        $form->addSelect('state', ('State'), $states)->setPrompt(_('-- select state --'));
        $form->addText('vs', _('Variable symbol'))->setHtmlType('number');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(PaymentModel $payment): string => 'alert alert-' .
            $payment->state->getBehaviorType();
        $this->setTitle(new TemplateItem($this->container, '@payment.payment_id'));
        $row = new RowContainer($this->container);
        $this->addRow($row, 'row');
        $row->addComponent(new TemplateItem($this->container, '@payment.price'), 'price');
        $row->addComponent(new TemplateItem($this->container, 'VS: @payment.variable_symbol'), 'vs');
        $row->addComponent(
            new TemplateItem($this->container, '@person.full_name (@person_info.email)'),
            'full_name'
        );
        $row->addComponent(new TemplateItem($this->container, '@event.role'), 'role');
        $items = new RelatedTable(
            $this->container,
            fn(PaymentModel $payment): TypedGroupedSelection => $payment->getSchedulePayment(),
            new Title(null, _('Items')),
            true
        );
        $this->addRow($items, 'items');
        $items->addColumn(
            new TemplateItem($this->container, _('@schedule_group.name_en: @schedule_item.name_en'), _('Item')),
            'name'
        );
        $items->addColumn(
            new TemplateItem(
                $this->container,
                '@person.full_name (@event.role)',
                _('For'),
                fn(SchedulePaymentModel $model) => $model->person_schedule
            ),
            'person'
        );
        $items->addColumn(
            new TemplateItem($this->container, '@schedule_item.price_czk / @schedule_item.price_eur', _('Price')),
            'price'
        );
        $this->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Detail')),
                fn(PaymentModel $model) => ['detail', ['id' => $model->getPrimary()]]
            ),
            'detail'
        );
    }
}
