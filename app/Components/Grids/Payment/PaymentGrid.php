<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;

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
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelPayment::class;
    }
}
