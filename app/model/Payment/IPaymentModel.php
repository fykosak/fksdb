<?php


namespace FKSDB\Payment;

/**
 * Interface IPaymentModel
 * @package FKSDB\Payment
 */
interface IPaymentModel {
    /**
     * @return Price
     */
    public function getPrice(): Price;
}
