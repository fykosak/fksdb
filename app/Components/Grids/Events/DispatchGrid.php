<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Fykosak\NetteORM\TypedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends EntityGrid<EventModel>
 */
class DispatchGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct(
            $container,
            EventService::class,
            [],
        );
    }

    /**
     * @phpstan-return TypedSelection<EventModel>
     */
    protected function getModels(): TypedSelection
    {
        $value = parent::getModels();
        $value->order('begin DESC');
        return $value;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();

        $this->paginate = true;
        $this->addColumns([
            'event.event_id',
        ]);
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(EventModel $model) => $model->getName()->getText($this->translator->lang) //@phpstan-ignore-line
            ),
            'event_name'
        );
        $this->addColumns([
            'contest.contest',
            'event.year',
            'event.role',
        ]);
        $this->addPresenterButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }
}
