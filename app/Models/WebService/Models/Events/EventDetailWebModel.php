<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;

/**
 * @phpstan-extends EventWebModel<array{eventId:int},array{
 *     teams?: mixed,
 *     participants?:mixed,
 *     schedule?:mixed,
 *     personSchedule?:mixed,
 * }>
 */
class EventDetailWebModel extends EventWebModel
{
    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function getJsonResponse(): array
    {
        return $this->getEvent()->__toArray();
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->eventAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $this->getEvent());
    }
}
