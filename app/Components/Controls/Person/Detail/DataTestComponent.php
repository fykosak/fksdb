<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\Logging\MemoryLogger;

class DataTestComponent extends BaseComponent
{
    private DataTestFactory $factory;

    final public function injectDataTestFactory(DataTestFactory $factory): void
    {
        $this->factory = $factory;
    }

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $logs = [];
            foreach ($this->factory->getTests('person') as $testId => $test) {
                $logger = new MemoryLogger();
                $test->run($logger, $this->person);
                $logs[$testId] = $logger->getMessages();
            }
            $this->template->render(
                __DIR__ . DIRECTORY_SEPARATOR . 'dataTest.latte',
                [
                    'logs' => $logs,
                    'tests' => $this->factory->getTests('person'),
                ]
            );
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_FULL;
    }
}
