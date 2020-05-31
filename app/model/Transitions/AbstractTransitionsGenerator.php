<?php

namespace FKSDB\Transitions;

/**
 * Class AbstractTransitionsGenerator
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractTransitionsGenerator {
    /**
     * @var string[]
     */
    protected array $emailData = [
        'blind_carbon_copy' => 'Fyziklání <fyziklani@fykos.cz>',
        // $data['carbon_copy']=
        'sender' => 'fyziklani@fykos.cz',
    ];
}
