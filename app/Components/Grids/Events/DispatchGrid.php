<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventTypeModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\EventTypeService;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseGrid<EventModel,array{
 *     event_type?:int,
 * }>
 */
final class DispatchGrid extends BaseGrid
{
    private EventService $service;
    private EventTypeService $eventTypeService;

    public function inject(EventService $service, EventTypeService $eventTypeService): void
    {
        $this->service = $service;
        $this->eventTypeService = $eventTypeService;
    }

    /**
     * @phpstan-return TypedSelection<EventModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable()->order('begin DESC');
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'event_type':
                    $query->where('event_type_id', $filterParam);
            }
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->filtered = true;
        $this->paginate = true;
        $this->counter = false;
        $this->addSimpleReferencedColumns([
            '@event.event_id',
        ]);
        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(EventModel $model) => $model->getName()->getText($this->translator->lang), //@phpstan-ignore-line
                new Title(null, _('Event name'))
            ),
            'event_name'
        );
        $this->addSimpleReferencedColumns([
            '@contest.contest',
            '@event.year',
            '@event.role',
        ]);
        $this->addPresenterButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }

    protected function configureForm(Form $form): void
    {
        $items = [];
        /** @var EventTypeModel $eventType */
        foreach ($this->eventTypeService->getTable() as $eventType) {
            $items[(string)$eventType->event_type_id] = $eventType->name;
        }
        $form->addSelect('event_type', _('Event type'), $items)->setPrompt(_('-- select event type --'));
    }
}
