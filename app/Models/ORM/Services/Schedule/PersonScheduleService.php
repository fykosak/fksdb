<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Schedule;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use Fykosak\NetteORM\Service;
use Nette\Application\BadRequestException;

/**
 * @phpstan-extends Service<PersonScheduleModel>
 */
final class PersonScheduleService extends Service
{
    /**
     * @throws BadRequestException
     */
    public function makeAttendance(PersonScheduleModel $personSchedule): void
    {
        if (
            in_array(
                $personSchedule->state->value,
                [PersonScheduleState::Participated, PersonScheduleState::Missed, PersonScheduleState::Cancelled]
            )
        ) {
            throw new \InvalidArgumentException(_('Transition unavailable'));
        }
        $personSchedule->checkPayment();
        $this->storeModel(['state' => PersonScheduleState::Participated], $personSchedule);
    }
}
