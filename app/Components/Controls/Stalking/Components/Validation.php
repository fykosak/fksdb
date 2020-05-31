<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\DataTestingFactory;

/**
 * Class Validation
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Validation extends AbstractStalkingComponent {

    private DataTestingFactory $validationFactory;

    public function injectDataTestingFactory(DataTestingFactory $dataTestingFactory): void {
        $this->validationFactory = $dataTestingFactory;
    }

    protected function getHeadline(): string {
        return _('Validation');
    }

    /**
     * @return int[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_RESTRICT, self::PERMISSION_FULL, self::PERMISSION_FULL];
    }

    public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, $userPermissions);
        $logger = new TestsLogger();
        foreach ($this->validationFactory->getTests('person') as $test) {
            $test->run($logger, $person);
        }

        $this->template->logs = $logger->getLogs();
        $this->template->setFile(__DIR__ . '/Validation.latte');
        $this->template->render();
    }
}
