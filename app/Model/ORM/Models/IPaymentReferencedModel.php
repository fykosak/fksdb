<?php

namespace FKSDB\Model\ORM\Models;
/**
 * Interface IPaymentReferencedModel
 * *
 */
interface IPaymentReferencedModel {
    public function getPayment(): ?ModelPayment;
}
