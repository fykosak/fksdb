<?php


namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PaymentGrid
 * @package FKSDB\Components\Grids\Payment
 */
abstract class PaymentGrid extends BaseGrid {
    /**
     * @var ServicePayment
     */
    protected $servicePayment;

    /**
     * PaymentGrid constructor.
     * @param ServicePayment $servicePayment
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServicePayment $servicePayment, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->servicePayment = $servicePayment;
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnPaymentId() {
        $this->addColumn('id', _('#'))->setRenderer(function ($row) {
            return '#' . ModelPayment::createFromActiveRow($row)->getPaymentId();
        });
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnPrice() {
        $this->addReflectionColumn(DbNames::TAB_PAYMENT, 'price', ModelPayment::class);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnState() {
        $this->addReflectionColumn(DbNames::TAB_PAYMENT, 'state', ModelPayment::class);
    }

    /**
     * @throws DuplicateButtonException
     */
    protected function addButtonDetail() {
        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:payment:detail', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnsSymbols() {
        //$this->addColumn('constant_symbol', _('CS'));
        $this->addReflectionColumn(DbNames::TAB_PAYMENT, 'variable_symbol', ModelPayment::class);
        // $this->addColumn('specific_symbol', _('SS'));
        // $this->addColumn('bank_account', _('Bank acc.'));
    }

}
