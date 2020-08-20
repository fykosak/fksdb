<?php

namespace FKSDB\Payment\Transition\Transitions;

use Exception;
use FKSDB\Transitions\Statements\Conditions\DateBetween;

/**
 * Class Fyziklani14Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Fyziklani14Payment extends PaymentTransitions {

    /**
     * @return callable
     * @throws Exception
     */
    protected function getDatesCondition(): callable {
        return new DateBetween('2020-01-01', '2020-02-13');
    }
}
