<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Rules;

use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use Nette\Forms\Controls\BaseControl;

class UniqueEmail {

    private ServicePersonInfo $servicePersonInfo;

    private ?ModelPerson $ignoredPerson;

    public function __construct(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function setIgnoredPerson(ModelPerson $ignoredPerson): void {
        $this->ignoredPerson = $ignoredPerson;
    }

    public function __invoke(BaseControl $control): bool {
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
