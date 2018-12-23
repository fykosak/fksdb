<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\ModelPayment;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;

class MyPaymentGrid extends BaseGrid {
    /**
     * @var \ServicePayment
     */
    private $servicePayment;

    function __construct(\ServicePayment $servicePayment) {
        parent::__construct();

        $this->servicePayment = $servicePayment;
    }

    /**
     * @param \BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $payments = $this->servicePayment->getTable()->where('person_id', $presenter->getUser()->getIdentity()->person_id)->order('payment_id DESC');

        $dataSource = new NDataSource($payments);
        $this->setDataSource($dataSource);

        /*
        * columns
        */
        /*$this->addColumn('display_name', _('Name'))->setRenderer(function ($row) {
            $person = ModelPayment::createFromTableRow($row)->getPerson();
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
            return $model->getPrice()->__toString();
        });
        $this->addColumn('state', _('Status'))->setRenderer(function ($row) {
            $class = ModelPayment::createFromTableRow($row)->getUIClass();
            return Html::el('span')->addAttributes(['class' => $class])->add(_($row->state));
        });

        /*
        /* operations
        */
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
