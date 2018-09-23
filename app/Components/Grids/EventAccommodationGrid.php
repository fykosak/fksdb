<?php

namespace FKSDB\Components\Grids;

use SQL\SearchableDataSource;


class EventAccommodationGrid extends BaseGrid {

    /**
     * @var \ServiceEventAccommodation
     */
    private $serviceEventAccommodation;

    private $eventId;

    function __construct($eventId, \ServiceEventAccommodation $serviceEventAccommodation) {
        parent::__construct();
        $this->eventId = $eventId;
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $accommodations = $this->serviceEventAccommodation->getTable()->where('event_id', $this->eventId);

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
            $model = \ModelEventAccommodation::createFromTableRow($row);
            return $model->getUsedCapacity() . '/' . $row->capacity;
        });
        $this->addButton('edit', _('Upravit'))->setText('Upravit')//todo i18n
        ->setLink(function ($row) {
            return $this->getPresenter()->link('edit', $row->event_accommodation_id);
        });
        $this->addButton('billeted', _('Accommodated persons'))->setText(_('Accommodated persons'))//todo i18n
        ->setLink(function ($row) {
            return $this->getPresenter()->link('billeted', ['eventAccommodationId' => $row->event_accommodation_id]);
        });

        $this->addButton('delete', _('Smazat'))->setClass('btn btn-sm btn-danger')->setText('Smazat')//todo i18n
        ->setLink(function ($row) {
            return $this->link('delete!', $row->event_accommodation_id);
        })->setConfirmationDialog(function () {
            return _('Opravdu smazat ubytovaní?'); //todo i18n
        });


        $this->addGlobalButton('add')
            ->setLabel('Přidat ubytovaní')
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
