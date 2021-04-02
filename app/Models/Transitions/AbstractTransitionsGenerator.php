<?php

namespace FKSDB\Models\Transitions;

abstract class AbstractTransitionsGenerator {
    protected array $emailData = [
        'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
        // $data['carbon_copy']=
        'sender' => 'fyziklani@fykos.cz',
    ];
}
