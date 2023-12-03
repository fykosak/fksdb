<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{eventId:int},(array{level:string,text:string})[]>
 */
class ReportsWebModel extends WebModel
{
    private EventService $eventService;
    private DataTestFactory $dataTestFactory;

    public function inject(EventService $eventService, DataTestFactory $dataTestFactory): void
    {
        $this->eventService = $eventService;
        $this->dataTestFactory = $dataTestFactory;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    public function getJsonResponse(array $params): array
    {
        set_time_limit(-1);

        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $tests = DataTestFactory::getEventTests($this->container);
        $logger = new MemoryLogger();
        foreach ($tests as $test) {
            $test->run($logger, $event);
        }
        return array_map(fn(Message $message) => $message->__toArray(), $logger->getMessages());
    }
}
