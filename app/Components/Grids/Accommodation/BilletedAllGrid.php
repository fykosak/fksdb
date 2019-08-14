<?php

namespace FKSDB\Components\Grids\Accommodation;

use EventModule\AccommodationPresenter;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventPersonAccommodation;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use Nette\Application\UI\InvalidLinkException;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class BilletedAllGrid
 * @package FKSDB\Components\Grids\Accommodation
 */
class BilletedAllGrid extends BilletedGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * BilletedAllGrid constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     */
    function __construct(ModelEvent $event, ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        parent::__construct($serviceEventPersonAccommodation);
        $this->event = $event;
    }

    /**
     * @param AccommodationPresenter $presenter
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $accommodations = $this->serviceEventPersonAccommodation->getTable()
            ->where('event_accommodation.event_id', $this->event->event_id)
            ->order('person_id');
        // $this->eventAccommodation->getAccommodated();
        $this->paginate = false;

        $dataSource = new NDataSource($accommodations);

        $this->setDataSource($dataSource);

        $this->addColumnPerson();
        $this->addColumnRole();

        $this->addColumn('event_accommodation_id', _('Accommodation'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromActiveRow($row);
            return $model->getEventAccommodation()->getLabel();
        });

        $this->addColumnPayment();
        $this->addColumnState();
    }
}
