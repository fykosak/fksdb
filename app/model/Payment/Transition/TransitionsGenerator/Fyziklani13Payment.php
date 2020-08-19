<?php

namespace FKSDB\Payment\Transition\Transitions;

use FKSDB\Authorization\EventAuthorizator;
use Closure;
use Exception;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEmailMessage;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\ITransitionsDecorator;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\Statements\Conditions\DateBetween;
use FKSDB\Transitions\Statements\Conditions\ExplicitEventRole;
use FKSDB\Transitions\Transition;
use FKSDB\Mail\MailTemplateFactory;
use Nette\Database\Connection;
use Tracy\Debugger;

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

    protected function getEmailDirectory(): string {
        return 'fyziklani/fyziklani2019/payment';
    }

    protected function getMachinePrefix(): string {
        return 'fyziklani13payment';
    }
}
