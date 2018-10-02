<?php

namespace FKSDB\Components\Forms\Rules;

use ModelPerson;
use Nette\Forms\Controls\BaseControl;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueEmail {

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ModelPerson
     */
    private $ignoredPerson;

    function __construct(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function getIgnoredPerson() {
        return $this->ignoredPerson;
    }

    public function setIgnoredPerson(ModelPerson $ignoredPerson = null) {
        $this->ignoredPerson = $ignoredPerson;
    }

    public function __invoke(BaseControl $control) {
        $email = $control->getValue();

        $conflicts = $this->servicePersonInfo->getTable()->where(['email' => $email]);
        if ($this->ignoredPerson && $this->ignoredPerson->person_id) {
            $conflicts->where('NOT person_id = ?', $this->ignoredPerson->person_id);
        }
        if (count($conflicts) > 0) {
            return false;
        }

        return true;
    }

}
