<?php

namespace FKSDB\Components\Grids\Accommodation;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\ModelEventPersonAccommodation;
use Nette\Utils\Html;

abstract class BilletedGrid extends BaseGrid {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    protected $serviceEventPersonAccommodation;

    function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        parent::__construct();
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addButton('confirmPayment', _('Confirm payment'))
            ->setClass('btn btn-sm btn-success')
            ->setText(_('Confirm payment'))
            ->setLink(function ($row) {
                return $this->link('confirmPayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status !== ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addButton('deletePayment', _('Delete payment'))->setText(_('Delete payment'))
            ->setClass('btn btn-sm btn-warning')
            ->setLink(function ($row) {
                return $this->link('deletePayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status == ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addGlobalButton('list', ['id' => null])
            ->setLabel(_('Zoznam ubytovaní'))
            ->setLink($this->getPresenter()->link('list'));

    }

    protected function addColumnPayment() {
        $this->addColumn('payment', _('Payment'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $modelPayment = $model->getPayment();
            if (!$modelPayment) {
                return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->add('No payment found');
            }
            return Html::el('span')->addAttributes(['class' => $modelPayment->getUIClass()])->add('#' . $modelPayment->getPaymentId() . '-' . $modelPayment->getStateLabel());
        });
    }

    protected function addColumnState() {
        $this->addColumn('status', _('State'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $classNames = ($model->status === ModelEventPersonAccommodation::STATUS_PAID) ? 'badge badge-success' : 'badge badge-danger';
            return Html::el('span')
                ->addAttributes(['class' => $classNames])
                ->add((($model->status == ModelEventPersonAccommodation::STATUS_PAID) ? _('Paid') : _('Waiting')));
        });
    }

    protected function addColumnName() {
        $this->addColumn('name', _('Name'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            return $model->getPerson()->getFullName();
        });
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleConfirmPayment($id) {
        $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
        $model = ModelEventPersonAccommodation::createFromTableRow($row);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            $this->redirect('this');
            return;
        }
        $model->update(['status' => ModelEventPersonAccommodation::STATUS_PAID]);
        $this->serviceEventPersonAccommodation->save($model);
        $this->redirect('this');
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDeletePayment($id) {
        $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
        $model = ModelEventPersonAccommodation::createFromTableRow($row);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            $this->redirect('this');
            return;
        }
        $model->update(['status' => ModelEventPersonAccommodation::STATUS_WAITING_FOR_PAYMENT]);
        $this->serviceEventPersonAccommodation->save($model);
        $this->redirect('this');
    }
}
