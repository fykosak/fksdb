<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\Transition\TransitionsGenerator;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Transitions\TransitionsGenerator\PaymentTransitions;

class Fyziklani16Payment extends PaymentTransitions
{
    /**
     * @throws \Exception
     */
    protected function getDatesCondition(): callable
    {
        throw new GoneException();
    }
}
