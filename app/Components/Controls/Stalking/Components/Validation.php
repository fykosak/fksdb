<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\DataTestingFactory;

/**
 * Class Validation
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Validation extends AbstractStalkingComponent {

    private DataTestingFactory $validationFactory;

    public function injectDataTestingFactory(DataTestingFactory $factory): void {
        $this->validationFactory = $factory;
    }

    public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Validation'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $logger = new MemoryLogger();
        foreach ($this->validationFactory->getTests('person') as $test) {
            $test->run($logger, $person);
        }

        $this->template->logs = $logger->getMessages();
        $this->template->setFile(__DIR__ . '/Validation.latte');
        $this->template->render();
    }
}
