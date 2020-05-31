<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use Nette\Forms\Controls\BaseControl;

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

    /**
     * UniqueEmail constructor.
     * @param ServicePersonInfo $servicePersonInfo
     */
    public function __construct(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    /**
     * @return ModelPerson
     */
    public function getIgnoredPerson() {
        return $this->ignoredPerson;
    }

    /**
     * @param ModelPerson|null $ignoredPerson
     */
    public function setIgnoredPerson(ModelPerson $ignoredPerson = null) {
        $this->ignoredPerson = $ignoredPerson;
    }

    public function __invoke(BaseControl $control): bool {
        $email = $control->getValue();

        $conflicts = $this->servicePersonInfo->getTable()->where(['email' => $email]);
        if ($this->ignoredPerson && $this->ignoredPerson->person_id) {
            $conflicts->where('NOT person_id = ?', $this->ignoredPerson->person_id);
        }
        return $conflicts->count() === 0;
    }
}
