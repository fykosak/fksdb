<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\ModelPerson;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueEmailFactory {

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    function __construct(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function create(ModelPerson $person = null) {
        $rule = new UniqueEmail($this->servicePersonInfo);
        $rule->setIgnoredPerson($person);

        return $rule;
    }

}
