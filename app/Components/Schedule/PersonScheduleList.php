<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\UI\EventRolePrinter;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseList<PersonModel,array{}>
 */
final class PersonScheduleList extends BaseList
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

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../Grids/Components/list.panel.latte';
    }

    /**
     * @phpstan-return TypedSelection<PersonModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->personService->getTable()->where(
            ':person_schedule.schedule_item.schedule_group.event_id',
            $this->event->event_id
        )->group('person_id');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = true;
        $this->setTitle(new SimpleItem($this->getContext(), '@person.full_name'));// @phpstan-ignore-line
        $row0 = $this->createRow();
        $row0->addComponent(
            new RendererItem(
                $this->container,
                fn(PersonModel $person) => EventRolePrinter::getHtml($person, $this->event),
                new Title(null, _('Role'))
            ),
            'role'
        );
        $row1 = $this->createRow();
        $relatedTable = new RelatedTable(
            $this->container,
            fn(PersonModel $person) => $person->getScheduleForEvent($this->event),  //@phpstan-ignore-line
            new Title(null, '')
        );
        $row1->addComponent($relatedTable, 'schedule');
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@schedule_item.name'),
            'item'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@schedule_group.name'),
            'group'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@schedule_item.price_czk/@schedule_item.price_eur', _('Price')),
            'price'
        );
        /** @phpstan-ignore-next-line */
        $relatedTable->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@payment.payment'),
            'payment'
        );
    }
}
