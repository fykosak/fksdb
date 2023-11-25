<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Components\DataTest\Tests\School\SchoolsProviderAdapter;
use FKSDB\Components\DataTest\Tests\School\VerifiedSchoolTest;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{eventId:int},(array{level:string,text:string})[]>
 */
class SchoolsReportsWebModel extends WebModel
{

    public function getExpectedParams(): Structure
    {
        return Expect::structure([]);
    }

    public function getJsonResponse(array $params): array
    {
        set_time_limit(-1);

        $tests = [
            new SchoolsProviderAdapter(new VerifiedSchoolTest($this->container), $this->container),
        ];
        $logger = new MemoryLogger();
        foreach ($tests as $test) {
            $test->run($logger, $this->user->getIdentity());
        }
        return array_map(fn(Message $message) => $message->__toArray(), $logger->getMessages());
    }
}
