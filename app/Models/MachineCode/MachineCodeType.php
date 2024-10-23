<?php

declare(strict_types=1);

namespace FKSDB\Models\MachineCode;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Service\Service;

enum MachineCodeType: string
{
    case Person = 'PE';
    case Team = 'TE';

    /**
     * @phpstan-return class-string<Service<EventParticipantModel|TeamModel2>>
     */
    public function getServiceClassName(): string
    {
        /** @phpstan-ignore-next-line */
        return match ($this) {
            self::Person => PersonService::class,
            self::Team => TeamService2::class,
        };
    }

    public static function fromModel(PersonModel|TeamModel2 $model): self
    {
        if ($model instanceof PersonModel) {
            return self::Person;
        } else {
            return self::Team;
        }
    }
}
