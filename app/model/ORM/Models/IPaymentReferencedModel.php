<?php

namespace FKSDB\ORM\Models;
/**
 * Interface IPaymentReferencedModel
 * *
 */
interface IPaymentReferencedModel {
    /**
     * @return ModelPayment|null
     */
    public function getPayment();
}
