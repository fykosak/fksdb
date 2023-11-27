<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\Logging\MemoryLogger;

/**
 * @phpstan-template THolder of ModelHolder
 */
trait OrganizerMailTrait
{
    private DataTestFactory $testFactory;

    public function injectDataTestFactory(DataTestFactory $testFactory): void
    {
        $this->testFactory = $testFactory;
    }

    /**
     * @param THolder $holder
     */
    protected function getMessageLog(ModelHolder $holder): MemoryLogger
    {
        $logger = new MemoryLogger();
        foreach ($this->testFactory->getTeamTests() as $test) {
            $test->run($logger, $holder->getModel());
        }
        return $logger;
    }
}
