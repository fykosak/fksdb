<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
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
            $this->template->render(
                __DIR__ . DIRECTORY_SEPARATOR . 'validation.latte',
                ['logs' => $logger->getMessages()]
            );
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_FULL;
    }
}
