<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events\Schedule;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-type TPersonSchedule array{person:array{name:string,personId:int,email:string|null},scheduleItemId:int}
 * @phpstan-extends WebModel<array{itemId:int},TPersonSchedule[]>
 */
class PersonListWebModel extends WebModel
{
    private ScheduleItemService $scheduleItemService;

    public function inject(ScheduleItemService $scheduleItemService): void
    {
        $this->scheduleItemService = $scheduleItemService;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'itemId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function getJsonResponse(): array
    {
        $item = $this->scheduleItemService->findByPrimary($this->params['itemId']);
        if (!$item) {
            throw new BadRequestException('Unknown item.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var PersonScheduleModel $model */
        foreach ($item->getInterested() as $model) {
            $data[] = [
                'person' => $model->person->__toArray(),
                'scheduleItemId' => $model->schedule_item_id,
            ];
        }
        return $data;
    }

    protected function isAuthorized(): bool
    {
        return false;
    }
}
