<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Roles\BaseRole;
use FKSDB\Models\Authorization\Roles\ContestRole;
use FKSDB\Models\Authorization\Roles\ContestYearRole;
use FKSDB\Models\Authorization\Roles\Events\ContestOrganizerRole;
use FKSDB\Models\Authorization\Roles\Events\EventOrganizerRole;
use FKSDB\Models\Authorization\Roles\Events\EventRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamTeacherRole;
use FKSDB\Models\Authorization\Roles\Events\ParticipantRole;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\Security\IIdentity;
use Nette\Utils\DateTime;

/**
 * @property-read int $login_id
 * @property-read int|null $person_id
 * @property-read PersonModel|null $person
 * @property-read string|null $login
 * @property-read string|null $hash
 * @property-read DateTime $created
 * @property-read DateTime|null $last_login
 * @property-read int $active
 */
final class LoginModel extends Model implements IIdentity
{
    /**
     * @throws \Throwable
     */
    public function __toString(): string
    {
        return $this->person ? $this->person->getFullName() : ($this->login ?? 'NAMELESS LOGIN');
    }

    /**
     * Sets hash of the instance with correct hashing function.
     *
     * @note Must be called after setting login_id.
     */
    public function calculateHash(string $password): string
    {
        return sha1($this->login_id . md5($password));
    }

    // ----- IIdentity implementation ----------

    public function getId(): int
    {
        return $this->login_id;
    }

    /**
     * @phpstan-return BaseRole[]
     */
    public function getRoles(): array
    {
        return [new BaseRole(BaseRole::Registered)];
    }

    /** @var ContestRole[][] */
    private array $contestRoles = [];

    /**
     * @phpstan-return ContestRole[]
     */
    public function getContestRoles(ContestModel $contest): array
    {
        if (!isset($this->contestRoles[$contest->contest_id])) {
            $this->contestRoles[$contest->contest_id] = [
                ...$this->getExplicitContestRoles($contest),
                ...$this->getImplicitContestRoles($contest),
            ];
        }
        return $this->contestRoles[$contest->contest_id];
    }

    /**
     * @phpstan-return ContestRole[]
     */
    public function getExplicitContestRoles(?ContestModel $contest = null): array
    {
        $grants = [];
        $query = $this->related(DbNames::TAB_CONTEST_GRANT, 'login_id');
        if ($contest) {
            $query->where('contest_id', $contest->contest_id);
        }
        /** @var ContestGrantModel $grant */
        foreach ($query as $grant) {
            $grants[] = new ContestRole($grant->role, $grant->contest);
        }
        return $grants;
    }

    /**
     * @phpstan-return ContestRole[]
     */
    public function getImplicitContestRoles(ContestModel $contest): array
    {
        $roles = [];
        if ($this->person) {
            foreach ($this->person->getActiveOrganizers() as $organizer) {
                if ($organizer->contest_id === $contest->contest_id) {
                    $roles[] = new ContestRole(
                        ContestRole::Organizer,
                        $organizer->contest,
                    );
                }
            }
            /** @var ContestantModel $contestant */
            foreach ($this->person->getContestants() as $contestant) {
                if ($contestant->contest_id === $contest->contest_id) {
                    $roles[] = new ContestRole(
                        ContestRole::Contestant,
                        $contestant->contest,
                    );
                }
            }
        }
        return $roles;
    }

    /** @var ContestYearRole[][][] */
    private array $contestYearRoles = [];

    /**
     * @phpstan-return ContestYearRole[]
     */
    public function getContestYearRoles(ContestYearModel $contestYear): array
    {
        if (!isset($this->contestYearRoles[$contestYear->contest_id][$contestYear->year])) {
            $this->contestYearRoles[$contestYear->contest_id] = $this->contestYearRoles[$contestYear->contest_id] ?? [];
            $this->contestYearRoles[$contestYear->contest_id][$contestYear->year] = $this->getImplicitContestYearRoles(
                $contestYear
            );
        }
        return $this->contestYearRoles[$contestYear->contest_id][$contestYear->year];
    }

    /**
     * @phpstan-return ContestYearRole[]
     */
    public function getImplicitContestYearRoles(ContestYearModel $contestYear): array
    {
        $roles = [];
        if ($this->person) {
            /** @var ContestantModel $contestant */
            foreach ($this->person->getContestants() as $contestant) {
                if ($contestant->contest_id === $contestYear->contest_id && $contestant->year === $contestYear->year) {
                    $roles[] = new ContestYearRole(
                        ContestRole::Contestant,
                        $contestant->getContestYear(),
                    );
                }
            }
        }
        return $roles;
    }

    /** @var EventRole[][] */
    private array $eventRoles = [];

    /**
     * @phpstan-return EventRole[]
     */
    public function getEventRoles(EventModel $event): array
    {
        if (!isset($this->eventRoles[$event->event_id])) {
            $this->eventRoles[$event->event_id] = [
                ...$this->getImplicitEventRoles($event),
                ...$this->getExplicitEventRoles($event),
            ];
        }
        return $this->eventRoles[$event->event_id];
    }

    /**
     * @phpstan-return EventRole[]
     */
    public function getExplicitEventRoles(EventModel $event): array
    {
        $grants = [];
        $query = $this->related(DbNames::TAB_EVENT_GRANT, 'login_id')
            ->where('event_id', $event->event_id);
        /** @var EventGrantModel $grant */
        foreach ($query as $grant) {
            $grants[] = new EventRole($grant->role, $grant->event);
        }
        return $grants;
    }

    /**
     * @phpstan-return EventRole[]
     */
    public function getImplicitEventRoles(EventModel $event): array
    {
        $roles = [];
        if ($this->person) {
            $teachers = $this->person->getTeamTeachers($event);
            if ($teachers->count('*')) {
                $teams = [];
                /** @var TeamTeacherModel $row */
                foreach ($teachers as $row) {
                    $teams[] = $row->fyziklani_team;
                }
                $roles[] = new TeamTeacherRole($event, $teams);
            }
            $eventOrganizer = $this->person->getEventOrganizer($event);
            if (isset($eventOrganizer)) {
                $roles[] = new EventOrganizerRole($event, $eventOrganizer);
            }
            $eventParticipant = $this->person->getEventParticipant($event);
            if (isset($eventParticipant)) {
                $roles[] = new ParticipantRole($event, $eventParticipant);
            }
            $teamMember = $this->person->getTeamMember($event);
            if ($teamMember) {
                $roles[] = new TeamMemberRole($event, $teamMember);
            }
            $organizer = $this->person->getActiveOrganizer($event->event_type->contest);
            if (isset($organizer)) {
                $roles[] = new ContestOrganizerRole($event, $organizer);
            }
        }
        return $roles;
    }

    /**
     * @phpstan-return TypedGroupedSelection<AuthTokenModel>
     */
    public function getTokens(AuthTokenType $type): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<AuthTokenModel> $query */
        $query = $this->related(DbNames::TAB_AUTH_TOKEN, 'login_id');
        $query->where('type', $type);
        return $query;
    }

    public function hasActiveToken(AuthTokenType $type): bool
    {
        /** @var AuthTokenModel $token */
        foreach ($this->getTokens($type) as $token) {
            if ($token->isActive()) {
                return true;
            }
        }
        return false;
    }
}
