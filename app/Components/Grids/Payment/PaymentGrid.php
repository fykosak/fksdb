<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;

/**
 * Class PaymentGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PaymentGrid extends BaseGrid {
    /**
     * @var ServicePayment
     */
    protected $servicePayment;

    /**
     * @param ServicePayment $servicePayment
     * @return void
     */
    public function injectServicePayment(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    protected function getModelClassName(): string {
        return ModelPayment::class;
    }
}
