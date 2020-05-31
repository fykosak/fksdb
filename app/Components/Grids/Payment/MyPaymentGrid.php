<?php

namespace FKSDB\Components\Grids\Payment;

use BasePresenter;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class MyPaymentGrid
 * @author Michal Červeňák <miso@fykos.cz>
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
            'payment.payment_uid',
            'event.event',
            'payment.price',
            'payment.state',
        ]);
        $this->addLink('payment.detail', true);
    }
}
