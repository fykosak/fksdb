<?php


namespace FKSDB\Payment;

/**
 * Interface IPaymentModel
 * @package FKSDB\Payment
 */
interface IPaymentModel {
    public function getPrice(): Price;
}
