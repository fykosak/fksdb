<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Application\UI\Presenter;
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

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->data->order('begin DESC');
        $this->addPresenterButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }
}
