<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class MyPaymentGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MyPaymentGrid extends PaymentGrid {

    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
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
