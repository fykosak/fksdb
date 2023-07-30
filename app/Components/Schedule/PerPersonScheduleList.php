<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Container\RelatedTable;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ValuePrinters\EventRolePrinter;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class PerPersonScheduleList extends BaseList
{
    private PersonService $personService;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    public function injectServices(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @phpstan-return TypedSelection<PersonModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->personService->getTable()->where(
            ':person_schedule.schedule_item.schedule_group.event_id',
            $this->event->event_id
        );
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->setTitle(new TemplateItem($this->getContext(), '@person.full_name'));
        $this->classNameCallback = fn() => 'alert alert-secondary';
        $row0 = new RowContainer($this->container);
        $this->addRow($row0, 'row0');
        $row0->addComponent(
            new RendererItem(
                $this->container,
                fn(PersonModel $person) => (new EventRolePrinter())($person, $this->event),
                new Title(null, '')
            ),
            'role'
        );
        $row1 = new RowContainer($this->container);
        $this->addRow($row1, 'row1');
        $relatedTable = new RelatedTable(
            $this->container,
            fn(PersonModel $person) => $person->getScheduleForEvent($this->event),
            new Title(null, '')
        );
        $row1->addComponent($relatedTable, 'schedule');
        $relatedTable->addColumn(new TemplateItem($this->container, '@schedule_item.name'), 'item');
        $relatedTable->addColumn(new TemplateItem($this->container, '@schedule_group.name'), 'group');
        $relatedTable->addColumn(
            new TemplateItem($this->container, '@schedule_item.price_czk/@schedule_item.price_eur'),
            'price'
        );
        $relatedTable->addColumn(new TemplateItem($this->container, '@payment.payment'), 'payment');
    }
}
