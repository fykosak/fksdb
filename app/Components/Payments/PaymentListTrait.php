<?php

declare(strict_types=1);

namespace FKSDB\Components\Payments;

use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;

trait PaymentListTrait
{
    protected function traitConfigure(): void
    {
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
    }
}
