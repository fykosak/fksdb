<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

class DispatchGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct(
            $container,
            EventService::class,
            [
                'event.event_id',
                'event.name',
                'contest.contest',
                'event.year',
                'event.role',
            ],
        );
    }

    protected function getModels(): Selection
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
        $this->addPresenterButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }
}
