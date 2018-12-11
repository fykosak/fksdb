<?php

namespace FKSDB\Components\Grids\EventPayment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelPayment;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;

class MyPaymentGrid extends BaseGrid {
    /**
     * @var \ServicePayment
     */
    private $serviceEventPayment;

    function __construct(\ServicePayment $serviceEventPayment) {
        parent::__construct();

        $this->serviceEventPayment = $serviceEventPayment;
    }

    /**
     * @param \BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $payments = $this->serviceEventPayment->getTable()->where('person_id', $presenter->getUser()->getIdentity()->person_id)->order('payment_id DESC');

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
            return '#' . ModelPayment::createFromTableRow($row)->getPaymentId();
        });;
        $this->addColumn('event', _('Event'))->setRenderer(function ($row) {
            return ModelPayment::createFromTableRow($row)->getEvent()->name;
        });;
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            $model = ModelPayment::createFromTableRow($row);
            return $model->price . ' ' . Price::getLabel($model->currency);
        });
        $this->addColumn('state', _('Status'))->setRenderer(function ($row) {
            $class = ModelPayment::createFromTableRow($row)->getUIClass();
            return Html::el('span')->addAttributes(['class' => $class])->add(_($row->state));
        });

        /*
        /* operations
        */
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setShow(function ($row) {
                return ModelPayment::createFromTableRow($row)->canEdit();
            })
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:payment:edit', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });

        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:payment:detail', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });
    }
}
