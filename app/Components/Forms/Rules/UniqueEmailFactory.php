<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePersonInfo;

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

    /**
     * UniqueEmailFactory constructor.
     * @param \FKSDB\ORM\Services\ServicePersonInfo $servicePersonInfo
     */
    public function __construct(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    /**
     * @param ModelPerson|null $person
     * @return UniqueEmail
     */
    public function create(ModelPerson $person = null) {
        $rule = new UniqueEmail($this->servicePersonInfo);
        $rule->setIgnoredPerson($person);

        return $rule;
    }

}
