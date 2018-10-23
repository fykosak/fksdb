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

        $accommodations = $this->serviceEventPersonAccommodation->getTable()->where('event_accommodation_id', $this->eventAccommodationId);

        $dataSource = new SearchableDataSource($accommodations);

        $this->setDataSource($dataSource);
        // $this->addColumn('name', _('Name'));
        $this->addColumn('name', _('Name'))->setRenderer(function ($row) {
            $model = \FKSDB\ORM\ModelEventPersonAccommodation::createFromTableRow($row);
            return $model->getPerson()->getFullName();
        });

        $this->addColumn('status', _('State'))->setRenderer(function ($row) {
            $model = \FKSDB\ORM\ModelEventPersonAccommodation::createFromTableRow($row);
            $classNames = ($model->status === \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_PAID) ? 'badge badge-success' : 'badge badge-danger';
            return Html::el('span')
                ->addAttributes(['class' => $classNames])
                ->add((($model->status == \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_PAID) ? _('Paid') : _('Waiting')));

        });

        $this->addButton('confirmPayment', _('Confirm payment'))
            ->setClass('btn btn-sm btn-success')
            ->setText(_('Confirm payment'))
            ->setLink(function ($row) {
                return $this->link('confirmPayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status !== \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addButton('deletePayment', _('Delete payment'))->setText(_('Delete payment'))
            ->setClass('btn btn-sm btn-warning')
            ->setLink(function ($row) {
                return $this->link('deletePayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status == \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_PAID;
            });

        /*/
                $this->addButton('confirmPaymentAll', _('Confirm all payment'))
                    ->setClass('btn btn-sm btn-success')
                    ->setText(_('Confirm all payment'))
                    ->setLink(function ($row) {
                        return $this->link('confirmPaymentAll!', $row->person_id);
                    });

                        $this->addButton('deletePaymentAll', _('Delete all payment'))->setText(_('Delete all payment'))
                            ->setClass('btn btn-sm btn-warning')
                            ->setLink(function ($row) {
                                return $this->link('deletePaymentAll!', $row->person_id);
                            });
        */
        $this->addGlobalButton('list')
            ->setLabel(_('Zoznam ubytovaní'))
            ->setLink($this->getPresenter()->link('list'));

    }

    public function handleConfirmPayment($id) {
        $model = $this->serviceEventPersonAccommodation->findByPrimary($id);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            $this->redirect('this');
            return;
        }
        $model->update(['status' => \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_PAID]);
        $this->serviceEventPersonAccommodation->save($model);
        $this->redirect('this');
    }

    public function handleDeletePayment($id) {
        $model = $this->serviceEventPersonAccommodation->findByPrimary($id);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            $this->redirect('this');
            return;
        }
        $model->update(['status' => \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_WAITING_FOR_PAYMENT]);
        $this->serviceEventPersonAccommodation->save($model);
        $this->redirect('this');
    }
    /*
        public function handleConfirmPaymentAll($personId) {
            $rows = $this->serviceEventPersonAccommodation->getTable()->where('person_id', $personId);
            foreach ($rows as $row) {
                $model = \FKSDB\ORM\ModelEventPersonAccommodation::createFromTableRow($row);
                $model->update(['status' => \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_PAID]);
                $this->serviceEventPersonAccommodation->save($model);
            }
            $this->redirect('this');
        }

        public function handleDeletePaymentAll($personId) {
            $rows = $this->serviceEventPersonAccommodation->getTable()->where('person_id', $personId);
            foreach ($rows as $row) {
                $model = \FKSDB\ORM\ModelEventPersonAccommodation::createFromTableRow($row);
                $model->update(['status' => \FKSDB\ORM\ModelEventPersonAccommodation::STATUS_WAITING_FOR_PAYMENT]);
                $this->serviceEventPersonAccommodation->save($model);
            }
            $this->redirect('this');
        }*/
}
