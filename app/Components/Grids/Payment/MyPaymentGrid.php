<?php

namespace FKSDB\Components\Grids\Payment;

use BasePresenter;
use FKSDB\ORM\Models\ModelPayment;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class MyPaymentGrid
 * @package FKSDB\Components\Grids\Payment
 */
class MyPaymentGrid extends PaymentGrid {

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $payments = $this->servicePayment->getTable()->where('person_id', $presenter->getUser()->getIdentity()->person_id)->order('payment_id DESC');

        $dataSource = new NDataSource($payments);
        $this->setDataSource($dataSource);

        $this->addColumnPaymentId();

        $this->addColumn('event', _('Event'))->setRenderer(function ($row) {
            return ModelPayment::createFromActiveRow($row)->getEvent()->name;
        });

        // $this->addColumnsSymbols();

        $this->addColumnPrice();

        $this->addColumnState();

        $this->addButtonDetail();
    }
}
