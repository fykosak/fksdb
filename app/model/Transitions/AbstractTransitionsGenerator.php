<?php

namespace FKSDB\Transitions;

/**
 * Class AbstractTransitionsGenerator
 * @package FKSDB\Transitions
 */
abstract class AbstractTransitionsGenerator {
    protected $emailData = [
        'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
        // $data['carbon_copy']=
        'sender' => 'fyziklani@fykos.cz',
    ];
}
