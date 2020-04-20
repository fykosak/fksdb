<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use Nette\DI\Container;

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
     * @param Container $container
     */
    function __construct(Container $container) {
        parent::__construct($container);
        $this->servicePayment = $container->getByType(ServicePayment::class);
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelPayment::class;
    }
}
