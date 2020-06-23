<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\DataTestingFactory;

/**
 * Class Validation
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Validation extends AbstractStalkingComponent {
    /**
     * @var DataTestingFactory
     */
    private $validationFactory;

    /**
     * @param DataTestingFactory $factory
     * @return void
     */
    public function injectDataTestingFactory(DataTestingFactory $factory) {
        $this->validationFactory = $factory;
    }

    protected function getHeadline(): string {
        return _('Validation');
    }

    protected function getMinimalPermissions(): int {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $logger = new MemoryLogger();
        foreach ($this->validationFactory->getTests('person') as $test) {
            $test->run($logger, $person);
        }

        $this->template->logs = $logger->getMessages();
        $this->template->setFile(__DIR__ . '/Validation.latte');
        $this->template->render();
    }
}
