<?php

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\DataTesting\DataTestingFactory;

class ValidationComponent extends BaseStalkingComponent {

    private DataTestingFactory $validationFactory;

    final public function injectDataTestingFactory(DataTestingFactory $factory): void {
        $this->validationFactory = $factory;
    }

    final public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Validation'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $logger = new MemoryLogger();
        foreach ($this->validationFactory->getTests('person') as $test) {
            $test->run($logger, $person);
        }

        $this->template->logs = $logger->getMessages();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.validation.latte');
    }
}
