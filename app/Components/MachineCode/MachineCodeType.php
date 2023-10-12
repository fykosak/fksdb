<?php

declare(strict_types=1);

namespace FKSDB\Components\MachineCode;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use Nette\Application\BadRequestException;
use Nette\InvalidStateException;

final class MachineCodeType extends FakeStringEnum
{
    public const Person = 'PE';
    public const Participant = 'EP';
    public const Team = 'TE';

    public static function cases(): array
    {
        return [
            new self(self::Person),
            new self(self::Participant),
            new self(self::Team),
        ];
    }

    /**
     * @return class-string<Service<Model>>
     */
    public function getServiceClassName(): string
    {
        switch ($this->value) {
            case self::Person:
                return PersonService::class;
            case self::Participant:
                return EventParticipantService::class;
            case self::Team:
                return TeamService2::class;
            default:
                throw new InvalidStateException();
        }
    }

    /**
     * @throws BadRequestException
     */
    public static function tryFromModel(Model $model): self
    {
        if ($model instanceof EventParticipantModel) {
            return new self(self::Participant);
        } elseif ($model instanceof TeamModel2) {
            return new self(self::Team);
        } elseif ($model instanceof PersonModel) {
            return new self(self::Person);
        }
        throw new BadRequestException(_('Wrong type of code.'));
    }
}
