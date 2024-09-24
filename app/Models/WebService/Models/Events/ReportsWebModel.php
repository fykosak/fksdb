<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;

/**
 * @phpstan-extends EventWebModel<array{eventId:int},(array{level:string,text:string})[]>
 */
class ReportsWebModel extends EventWebModel
{
    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(): array
    {
        set_time_limit(-1);
        $tests = DataTestFactory::getEventTests($this->container);
        $logger = new TestLogger();
        foreach ($tests as $test) {
            $test->run($logger, $this->getEvent());
        }
        return array_map(
            fn(TestMessage $message) => ['text' => $message->toText(), 'level' => $message->level],
            $logger->getMessages()
        );
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource(RestApiPresenter::RESOURCE_ID, $this->getEvent()),
            self::class,
            $this->getEvent()
        );
    }
}
