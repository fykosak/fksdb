<?php

namespace FKSDB\Models\Payment;

interface PaymentModel {
    public function getPrice(): Price;
}
