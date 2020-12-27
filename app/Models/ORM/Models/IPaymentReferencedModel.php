<?php

namespace FKSDB\Models\ORM\Models;
/**
 * Interface IPaymentReferencedModel
 * *
 */
interface IPaymentReferencedModel {
    public function getPayment(): ?ModelPayment;
}
