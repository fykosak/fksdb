<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseList<PaymentModel,array{
 *     state?:string,
 *     vs?:string,
 * }>
 */
final class PaymentList extends BaseList
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
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'state':
                    $query->where('state', $filterParam);
                    break;
                case 'vs':
                    $query->where('variable_symbol', $filterParam);
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

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../Components/list.panel.latte';
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = true;
        $this->counter = true;
        /** @phpstan-ignore-next-line */
        $this->setTitle(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@payment.payment_id @payment.state')
        );
        $row = $this->createRow();
        $row->addComponent(new SimpleItem($this->container, '@payment.price'), 'price');
        $row->addComponent(new TemplateItem($this->container, 'VS: @payment.variable_symbol'), 'vs');
        $row->addComponent(
            new TemplateItem($this->container, '@person.full_name (@person_info.email)'),
            'full_name'
        );
        $row->addComponent(new SimpleItem($this->container, '@event.role'), 'role');
        /** @phpstan-var RelatedTable<PaymentModel,SchedulePaymentModel> $items */
        $items = $this->addRow(
            new RelatedTable(
                $this->container,
                /** @phpstan-ignore-next-line */
                fn(PaymentModel $payment): TypedGroupedSelection => $payment->getSchedulePayment(),
                new Title(null, _('Items')),
                true
            ),
            'items'
        );
        /** @phpstan-ignore-next-line */
        $items->addTableColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, _('@schedule_group.name_en: @schedule_item.name_en'), _('Item')),
            'name'
        );
        /** @phpstan-ignore-next-line */
        $items->addTableColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem(
                $this->container,
                '@person.full_name (@event.role)',
                _('For'),
                fn(SchedulePaymentModel $model) => $model->person_schedule
            ),
            'person'
        );
        /** @phpstan-ignore-next-line */
        $items->addTableColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@schedule_item.price_czk / @schedule_item.price_eur', _('Price')),
            'price'
        );
        $this->addPresenterButton(
            ':Event:Payment:detail',
            'detail',
            _('button.detail'),
            false,
            ['id' => 'payment_id']
        );
    }
}
