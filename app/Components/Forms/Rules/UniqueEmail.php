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

    private ServicePersonInfo $servicePersonInfo;

    private ?ModelPerson $ignoredPerson = null;

    /**
     * UniqueEmail constructor.
     * @param ServicePersonInfo $servicePersonInfo
     */
    public function __construct(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function getIgnoredPerson(): ?ModelPerson {
        return $this->ignoredPerson;
    }

    public function setIgnoredPerson(?ModelPerson $ignoredPerson): void {
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
