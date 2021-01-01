<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class DispatchGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchGrid extends EntityGrid {

    public function __construct(Container $container) {
        parent::__construct($container, ServiceEvent::class, [
                'event.event_id',
                'event.name',
                'contest.contest',
                'event.year',
                'event.role',
            ]
        );
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->setDefaultOrder('begin DESC');
        $this->addLinkButton('Dashboard:default', 'detail', _('Detail'), false, ['eventId' => 'event_id']);
    }
}
