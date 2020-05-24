<?php


namespace FKSDB\Payment;

/**
 * Interface IPaymentModel
 * *
 */
interface IPaymentModel {
    public function getPrice(): Price;
}
