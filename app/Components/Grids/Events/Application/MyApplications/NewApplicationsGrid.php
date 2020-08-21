<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class NewApplicationsGrid extends BaseGrid {

    protected ServiceEvent $serviceEvent;

    public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    protected function getData(): IDataSource {
        $events = $this->serviceEvent->getTable()
            // ->where('event_type.contest_id', $this->contest->contest_id)
            ->where('registration_begin <= NOW()')
            ->where('registration_end >= NOW()');
        return new NDataSource($events);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns(['event.name']);
        $this->addButton('create')->setText(_('Create application'))->setLink(function (ModelEvent $row): string {
            return $this->getPresenter()->link(':Public:Application:default', ['eventId' => $row->event_id]);
        });
    }
}
