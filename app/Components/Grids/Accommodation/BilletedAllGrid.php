<?php

namespace FKSDB\Components\Grids\Accommodation;

use EventModule\AccommodationPresenter;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPersonAccommodation;
use NiftyGrid\DataSource\NDataSource;

class BilletedAllGrid extends BilletedGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    function __construct(ModelEvent $event, \ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        parent::__construct($serviceEventPersonAccommodation);
        $this->event = $event;
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

        $accommodations = $this->serviceEventPersonAccommodation->getTable()
            ->where('event_accommodation.event_id', $this->event->event_id)
            ->order('person_id');
        // $this->eventAccommodation->getAccommodated();
        $this->paginate = false;

        $dataSource = new NDataSource($accommodations);

        $this->setDataSource($dataSource);

        $this->addColumnName();

        $this->addColumn('accommodation', _('Accommodation'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            return $model->getEventAccommodation()->getLabel();
        });

        $this->addColumnPayment();
        $this->addColumnState();
    }
}
