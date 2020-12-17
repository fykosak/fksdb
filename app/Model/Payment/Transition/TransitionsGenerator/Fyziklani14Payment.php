<?php

namespace FKSDB\Model\Payment\Transition\TransitionsGenerator;

use Exception;
use FKSDB\Model\Transitions\Transition\Statements\Conditions\DateBetween;

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
