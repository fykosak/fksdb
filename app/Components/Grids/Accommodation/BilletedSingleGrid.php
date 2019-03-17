<?php

namespace FKSDB\Components\Grids\Accommodation;

use EventModule\AccommodationPresenter;
use FKSDB\ORM\Models\ModelEventAccommodation;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use NiftyGrid\DataSource\NDataSource;

/**
 * Class BilletedSingleGrid
 * @package FKSDB\Components\Grids\Accommodation
 */
class BilletedSingleGrid extends BilletedGrid {
    /**
     * @var ModelEventAccommodation
     */
    private $eventAccommodation;

    /**
     * BilletedSingleGrid constructor.
     * @param ModelEventAccommodation $eventAccommodation
     * @param ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     */
    function __construct(ModelEventAccommodation $eventAccommodation, ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        parent::__construct($serviceEventPersonAccommodation);
        $this->eventAccommodation = $eventAccommodation;
    }

    /**
     * @param AccommodationPresenter $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $accommodations = $this->serviceEventPersonAccommodation->getTable()->where('event_accommodation_id', $this->eventAccommodation->event_accommodation_id);
        $this->paginate = false;

        $dataSource = new NDataSource($accommodations);
        $this->setDataSource($dataSource);

        $this->addColumnPerson();

        $this->addColumnPayment();

        $this->addColumnState();

    }
}
