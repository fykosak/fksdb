<?php

namespace FKSDB\Components\Grids\Accommodation;

use EventModule\AccommodationPresenter;
use FKSDB\ORM\ModelEventAccommodation;
use NiftyGrid\DataSource\NDataSource;

class BilletedSingleGrid extends BilletedGrid {
    /**
     * @var ModelEventAccommodation
     */
    private $eventAccommodation;

    function __construct(ModelEventAccommodation $eventAccommodation, \ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
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
