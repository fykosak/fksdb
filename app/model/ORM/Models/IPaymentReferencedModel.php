<?php

namespace FKSDB\ORM\Models;
/**
 * Interface IPaymentReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IPaymentReferencedModel {
    /**
     * @return ModelPayment|null
     */
    public function getPayment();
}
