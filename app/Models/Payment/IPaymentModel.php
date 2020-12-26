<?php


namespace FKSDB\Models\Payment;

/**
 * Interface IPaymentModel
 * *
 */
interface IPaymentModel {
    public function getPrice(): Price;
}
