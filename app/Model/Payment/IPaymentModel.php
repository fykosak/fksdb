<?php


namespace FKSDB\Model\Payment;

/**
 * Interface IPaymentModel
 * *
 */
interface IPaymentModel {
    public function getPrice(): Price;
}
