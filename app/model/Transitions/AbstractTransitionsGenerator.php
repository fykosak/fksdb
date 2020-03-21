<?php

namespace FKSDB\Transitions;

use FKSDB\ORM\Models\ModelEmailMessage;

/**
 * Class AbstractTransitionsGenerator
 * @package FKSDB\Transitions
 */
abstract class AbstractTransitionsGenerator {
    protected $emailData = [
        'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
        // $data['carbon_copy']=
        'sender' => 'fyziklani@fykos.cz',
        'reply_to' => 'Fyziklání <fyziklani@fykos.cz>',
        'state' => ModelEmailMessage::STATE_WAITING,
    ];
}
