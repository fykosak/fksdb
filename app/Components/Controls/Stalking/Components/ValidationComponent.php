<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\Logging\MemoryLogger;

class ValidationComponent extends BaseStalkingComponent
{
    private DataTestingFactory $validationFactory;

    final public function injectDataTestingFactory(DataTestingFactory $factory): void
    {
        $this->validationFactory = $factory;
    }

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $logger = new MemoryLogger();
            foreach ($this->validationFactory->getTests('person') as $test) {
                $test->run($logger, $this->person);
            }

            $this->template->logs = $logger->getMessages();
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.validation.latte');
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_FULL;
    }
}
