<?php

namespace FKSDB\Components\Grids;

use EventModule\AccommodationPresenter;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventAccommodation;
use SQL\SearchableDataSource;


class EventAccommodationGrid extends BaseGrid {

    /**
     * @var \ServiceEventAccommodation
     */
    private $serviceEventAccommodation;
    /**
     * @var ModelEvent
     */
    private $event;

    function __construct(ModelEvent $event, \ServiceEventAccommodation $serviceEventAccommodation) {
        parent::__construct();
        $this->event = $event;
        $this->serviceEventAccommodation = $serviceEventAccommodation;
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
        $accommodations =$this->event->getEventAccommodations();

        $dataSource = new SearchableDataSource($accommodations);

        $this->setDataSource($dataSource);
        $this->addColumn('name', _('Name'));
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            return $row->price_kc . ' Kč/' . $row->price_eur . ' €';
        });
        $this->addColumn('date', _('Date'))->setRenderer(function ($row) {
            return $row->date->format('Y-m-d');
        });
        $this->addColumn('capacity', _('Capacity'))->setRenderer(function ($row) {
            $model = ModelEventAccommodation::createFromTableRow($row);
            return $model->getUsedCapacity() . '/' . $row->capacity;
        });
        $this->addButton('edit', _('Edit'))->setText(_('Edit'))
        ->setLink(function ($row) {
            return $this->getPresenter()->link('edit', ['id' => $row->event_accommodation_id]);
        });
        $this->addButton('billeted', _('Accommodated persons'))->setText(_('Accommodated persons'))
        ->setLink(function ($row) {
            return $this->getPresenter()->link('billeted', ['id' => $row->event_accommodation_id]);
        });

        $this->addButton('delete', _('Remove'))->setClass('btn btn-sm btn-danger')->setText(_('Remove'))
        ->setLink(function ($row) {
            return $this->link('delete!', $row->event_accommodation_id);
        })->setConfirmationDialog(function () {
            return _('Opravdu smazat ubytovaní?');
        });

        $this->addGlobalButton('add')
            ->setLabel(_('Přidat ubytovaní'))
            ->setLink($this->getPresenter()->link('create'));


    }

    public function handleDelete($id) {
        $model = $this->serviceEventAccommodation->findByPrimary($id);
        if (!$model) {
            $this->flashMessage(_('some another bullshit'));
            return;
        }
        try {
            $model->delete();
        } catch (\PDOException $exception) {
            if ($exception->getCode() == 23000) {
                $this->flashMessage(_('Nelze zmazat ubytovaní, když je nekto ubytovaný'), 'danger');
                $this->redirect('this');
            };
        };
        $this->redirect('this');
    }
}
