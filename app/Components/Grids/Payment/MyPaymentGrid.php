<?php

namespace FKSDB\Components\Grids\Payment;

use BasePresenter;
use FKSDB\NotImplementedException;
use FKSDB\ORM\DbNames;
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
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $payments = $this->servicePayment->getTable()->where('person_id', $presenter->getUser()->getIdentity()->person_id)->order('payment_id DESC');

        $dataSource = new NDataSource($payments);
        $this->setDataSource($dataSource);

        $this->addColumns([
            DbNames::TAB_PAYMENT . '.id',
            // 'referenced.event_name',
            DbNames::TAB_PAYMENT . '.price',
            DbNames::TAB_PAYMENT . '.state',
        ]);

        $this->addColumn('event', _('Event'))->setRenderer(function (ModelPayment $payment) {
            return $payment->getEvent()->name;
        });
        $this->addLink('payment.detail', true);
    }
}
