<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\Utils\Logging\MemoryLogger;

class ValidationComponent extends BaseComponent
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
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'validation.latte');
        }
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Full;
    }
}
