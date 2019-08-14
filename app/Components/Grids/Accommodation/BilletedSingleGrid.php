<?php

namespace FKSDB\Components\Grids\Accommodation;

use EventModule\AccommodationPresenter;
use FKSDB\ORM\Models\ModelEventAccommodation;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use Nette\Application\UI\InvalidLinkException;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

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
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
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
