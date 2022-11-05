<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Rules;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonInfoService;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;

class UniqueEmail
{
    private PersonInfoService $personInfoService;
    private ?PersonModel $ignoredPerson = null;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(PersonInfoService $personInfoService): void
    {
        $this->personInfoService = $personInfoService;
    }

    public function setIgnoredPerson(PersonModel $ignoredPerson): void
    {
        $this->ignoredPerson = $ignoredPerson;
    }

    public function __invoke(BaseControl $control): bool
    {
        $email = $control->getValue();

        $conflicts = $this->personInfoService->getTable()->where(['email' => $email]);
        if ($this->ignoredPerson && $this->ignoredPerson->person_id) {
            $conflicts->where('NOT person_id = ?', $this->ignoredPerson->person_id);
        }
        if (count($conflicts) > 0) {
            return false;
        }
        return true;
    }
}
