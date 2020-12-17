<?php

namespace FKSDB\Model\Payment\Transition\TransitionsGenerator;

use Exception;
use FKSDB\Model\Transitions\Transition\Statements\Conditions\DateBetween;

/**
 * Class Fyziklani13Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */

class Fyziklani13Payment extends PaymentTransitions {

    /**
     * @return callable
     * @throws Exception
     */
    protected function getDatesCondition(): callable {
        return new DateBetween('2019-01-21', '2019-02-15');
    }
}
