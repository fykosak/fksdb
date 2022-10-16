<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\Transition\TransitionsGenerator;

use FKSDB\Models\Transitions\Transition\Statements\Conditions\DateBetween;
use FKSDB\Models\Transitions\TransitionsGenerator\PaymentTransitions;

class Fyziklani13Payment extends PaymentTransitions
{
    /**
     * @throws \Exception
     */
    protected function getDatesCondition(): callable
    {
        return new DateBetween('2019-01-21', '2019-02-15');
    }
}
