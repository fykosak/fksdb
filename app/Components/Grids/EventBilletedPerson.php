<?php

namespace FKSDB\Components\Grids;

use Nette\Utils\Html;
use SQL\SearchableDataSource;

class EventBilletedPerson extends BaseGrid {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;

    private $eventAccommodationId;

    function __construct($eventAccommodationId, \ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        parent::__construct();
        $this->eventAccommodationId = $eventAccommodationId;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.v4.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.v4.latte');

        $accommodations = $this->serviceEventPersonAccommodation->getTable()->where('event_accommodation_id', $this->eventAccommodationId);

        $dataSource = new SearchableDataSource($accommodations);

        $this->setDataSource($dataSource);
        // $this->addColumn('name', _('Name'));
        $this->addColumn('name', _('Name'))->setRenderer(function ($row) {
            $model = \ModelEventPersonAccommodation::createFromTableRow($row);
            return $model->getPerson()->getFullName();
        });

        $this->addColumn('status', _('State'))->setRenderer(function ($row) {
            $model = \ModelEventPersonAccommodation::createFromTableRow($row);
            $classNames = ($model->status === \ModelEventPersonAccommodation::STATUS_PAID) ? 'badge badge-success' : 'badge badge-danger';
            return Html::el('span')
                ->addAttributes(['class' => $classNames])
                ->add((($model->status == \ModelEventPersonAccommodation::STATUS_PAID) ? _('Paid') : _('Waiting')));

        });

        $this->addButton('confirmPayment', _('Confirm payment'))
            ->setClass('btn btn-sm btn-success')
            ->setText(_('Confirm payment'))
            ->setLink(function ($row) {
                return $this->link('confirmPayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status !== \ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addButton('deletePayment', _('Delete payment'))->setText(_('Delete payment'))
            ->setClass('btn btn-sm btn-warning')
            ->setLink(function ($row) {
                return $this->link('deletePayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status == \ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addGlobalButton('list')
            ->setLabel(_('Zoznam ubytovaní'))
            ->setLink($this->getPresenter()->link('list'));

    }

    public function handleConfirmPayment($id) {
        $model = $this->serviceEventPersonAccommodation->findByPrimary($id);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            return;
        }
        $model->update(['status' => \ModelEventPersonAccommodation::STATUS_PAID]);
        $this->serviceEventPersonAccommodation->save($model);
    }

    public function handleDeletePayment($id) {
        $model = $this->serviceEventPersonAccommodation->findByPrimary($id);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            return;
        }
        $model->update(['status' => \ModelEventPersonAccommodation::STATUS_WAITING_FOR_PAYMENT]);
        $this->serviceEventPersonAccommodation->save($model);
    }
}
