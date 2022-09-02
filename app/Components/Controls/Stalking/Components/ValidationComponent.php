<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\DataTesting\DataTestingFactory;

class ValidationComponent extends BaseStalkingComponent
{
    private DataTestingFactory $validationFactory;

    final public function injectDataTestingFactory(DataTestingFactory $factory): void
    {
        $this->validationFactory = $factory;
    }

    final public function render(PersonModel $person, int $userPermissions): void
    {
        $this->beforeRender($person, _('Validation'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $logger = new MemoryLogger();
        foreach ($this->validationFactory->getTests('person') as $test) {
            $test->run($logger, $person);
        }

        $this->getTemplate()->logs = $logger->getMessages();
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.validation.latte');
    }
}
