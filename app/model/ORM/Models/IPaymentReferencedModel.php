<?php

namespace FKSDB\ORM\Models;
/**
 * Interface IPaymentReferencedModel
 * *
 */
interface IPaymentReferencedModel {
    public function getPayment(): ?ModelPayment;
}
