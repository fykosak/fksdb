<?php

namespace FKSDB\Models\Payment\Transition\TransitionsGenerator;


use Exception;
use FKSDB\Models\Transitions\Transition\Statements\Conditions\DateBetween;
use FKSDB\Models\Transitions\TransitionsGenerator\PaymentTransitions;

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
