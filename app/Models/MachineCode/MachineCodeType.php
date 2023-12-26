<?php

declare(strict_types=1);

namespace FKSDB\Models\MachineCode;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamTeacherService;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Nette\Application\BadRequestException;
use Nette\InvalidStateException;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 */
final class MachineCodeType extends FakeStringEnum
{
    // phpcs:disable
    public const Participant = 'EP';
    public const Team = 'TE';
    public const TeamMember = 'TM';
    public const TeamTeacher = 'TT';

    // phpcs:enable

    public static function cases(): array
    {
        return [
            new self(self::Participant),
            new self(self::Team),
            new self(self::TeamMember),
            new self(self::TeamTeacher),
        ];
    }

    /**
     * @phpstan-return class-string<Service<TSupportedModel>>
     */
    public function getServiceClassName(): string
    {
        switch ($this->value) {
            case self::TeamTeacher:
                return TeamTeacherService::class;// @phpstan-ignore-line
            case self::TeamMember:
                return TeamMemberService::class;// @phpstan-ignore-line
            case self::Participant:
                return EventParticipantService::class;// @phpstan-ignore-line
            case self::Team:
                return TeamService2::class;// @phpstan-ignore-line
            default:
                throw new InvalidStateException();
        }
    }

    /**
     * @throws BadRequestException
     * @phpstan-param TSupportedModel $model
     */
    public static function fromModel(Model $model): self
    {
        if ($model instanceof EventParticipantModel) {
            return new self(self::Participant);
        } elseif ($model instanceof TeamModel2) {
            return new self(self::Team);
        } elseif ($model instanceof TeamMemberModel) {
            return new self(self::TeamMember);
        } elseif ($model instanceof TeamTeacherModel) {
            return new self(self::TeamTeacher);
        }
        throw new BadRequestException(_('Wrong type of code.')); //@phpstan-ignore-line
    }
}
