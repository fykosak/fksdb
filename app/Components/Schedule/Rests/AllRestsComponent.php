<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Rests;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\Database\Table\Selection;

/**
 * @phpstan-extends BaseGrid<PersonScheduleModel>
 */
class AllRestsComponent extends BaseGrid
{
    private PersonScheduleService $personScheduleService;

    public function inject(PersonScheduleService $personScheduleService): void
    {
        $this->personScheduleService = $personScheduleService;
    }

    protected function configure(): void
    {
        $this->counter = false;
        $this->paginate = false;
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@event.name'), 'event');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@schedule_group.name'), 'schedule_group');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@schedule_item.name'), 'schedule_item');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@person.full_name'), 'person');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@schedule_item.price_eur'), 'price_eur');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@schedule_item.price_czk'), 'price_czk');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@person_schedule.payment_deadline'), 'deadline');
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(new SimpleItem($this->container, '@payment.payment'), 'payment');
    }

    protected function getModels(): Selection
    {
        /** @var TypedSelection<PersonScheduleModel> $query */
        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.payable = TRUE')
            ->whereOr([
                ':schedule_payment.payment.payment_id IS NULL',
                ':schedule_payment.payment.state NOT ?' => PaymentState::RECEIVED
            ]);
        return $query;
    }
}
