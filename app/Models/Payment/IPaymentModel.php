<?php


namespace FKSDB\Models\Payment;

interface IPaymentModel {
    public function getPrice(): Price;
}
