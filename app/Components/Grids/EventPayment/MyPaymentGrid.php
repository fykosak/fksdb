<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\ModelEventPayment;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;

class MyPaymentGrid extends BaseGrid {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;

    function __construct(\ServiceEventPayment $serviceEventPayment) {
        parent::__construct();

        $this->serviceEventPayment = $serviceEventPayment;
    }

    /**
     * @param \BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $payments = $this->serviceEventPayment->getTable()->where('person_id', $presenter->getUser()->getIdentity()->person_id);

        $dataSource = new NDataSource($payments);
        $this->setDataSource($dataSource);

        /*
        * columns
        */
        /*$this->addColumn('display_name', _('Name'))->setRenderer(function ($row) {
            $person = ModelEventPayment::createFromTableRow($row)->getPerson();
            return $person->getFullname();
        });*/
        $this->addColumn('id', _('#'))->setRenderer(function ($row) {
            return '#' . ModelEventPayment::createFromTableRow($row)->getPaymentId();
        });;
        $this->addColumn('event', _('Event'))->setRenderer(function ($row) {
            return ModelEventPayment::createFromTableRow($row)->getEvent()->name;
        });;
        $this->addColumn('price_kc', _('Price Kč'));
        $this->addColumn('price_eur', _('Price €'));
        $this->addColumn('state', _('Status'))->setRenderer(function ($row) {
            $class = ModelEventPayment::createFromTableRow($row)->getUIClass();
            return Html::el('span')->addAttributes(['class' => $class])->add(_($row->state));
        });

        /*
        /* operations
        */
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setShow(function ($row) {
                return ModelEventPayment::createFromTableRow($row)->canEdit();
            })
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });

        $this->addButton('confirm', _('Confirm'))
            ->setText(_('Confirm'))
            ->setShow(function ($row) {
                return ModelEventPayment::createFromTableRow($row)->state === ModelEventPayment::STATE_NEW;
            })
            ->setLink(function ($row) {
                return $this->getPresenter()->link('confirm', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });

        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('detail', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });
    }
}
