<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\EventRole\{ContestOrgRole,
    EventOrgRole,
    EventRole,
    FyziklaniTeamMemberRole,
    FyziklaniTeamTeacherRole,
    ParticipantRole
};
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int $person_id
 * @property-read string $family_name
 * @property-read string $other_name
 * @property-read string|null $born_family_name
 * @property-read string|null $display_name
 * @property-read PersonGender $gender
 * @property-read \DateTimeInterface $created
 */
final class PersonModel extends Model implements Resource
{

    public const RESOURCE_ID = 'person';

    /**
     * Returns first of the person's logins.
     * (so far, there's not support for multiple login in DB schema)
     */
    public function getLogin(): ?LoginModel
    {
        /** @var LoginModel|null $login */
        $login = $this->related(DbNames::TAB_LOGIN, 'person_id')->fetch();
        return $login;
    }

    public function getTeacher(): ?TeacherModel
    {
        /** @var TeacherModel|null $teacher */
        $teacher = $this->related(DbNames::TAB_TEACHER, 'person_id')->fetch();
        return $teacher;
    }

    public function getPreferredLang(): ?string
    {
        return $this->getInfo() ? $this->getInfo()->preferred_lang : null;
    }

    public function getInfo(): ?PersonInfoModel
    {
        /** @var PersonInfoModel|null $info */
        $info = $this->related(DbNames::TAB_PERSON_INFO, 'person_id')->fetch();
        return $info;
    }

    public function getHistoryByContestYear(ContestYearModel $contestYear): ?PersonHistoryModel
    {
        /** @var PersonHistoryModel|null $history */
        $history = $this->getHistories()
            ->where('ac_year', $contestYear->ac_year)
            ->fetch();
        return $history;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PersonHistoryModel>
     */
    public function getHistories(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id');
    }

    public function getHistory(int $acYear): ?PersonHistoryModel
    {
        /** @var PersonHistoryModel|null $history */
        $history = $this->getHistories()
            ->where('ac_year', $acYear)
            ->fetch();
        return $history;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ContestantModel>
     */
    public function getContestants(?ContestModel $contest = null): TypedGroupedSelection
    {
        $related = $this->related(DbNames::TAB_CONTESTANT, 'person_id');
        if ($contest) {
            $related->where('contest_id', $contest->contest_id);
        }
        return $related;
    }

    public function getContestantByContestYear(ContestYearModel $contestYear): ?ContestantModel
    {
        /** @var ContestantModel|null $contestant */
        $contestant = $this->getContestants($contestYear->contest)->where('year', $contestYear->year)->fetch();
        return $contestant;
    }

    /**
     * @deprecated
     * @phpstan-return TypedGroupedSelection<OrgModel>
     */
    public function getOrgs(?int $contestId = null): TypedGroupedSelection
    {
        $related = $this->getOrganisers();
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PersonHasFlagModel>
     */
    public function getFlags(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_HAS_FLAG, 'person_id');
    }

    public function hasPersonFlag(string $flagType): ?PersonHasFlagModel
    {
        /** @var PersonHasFlagModel|null $personFlag */
        $personFlag = $this->getFlags()->where('flag.fid', $flagType)->fetch();
        return $personFlag;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PostContactModel>
     */
    public function getPostContacts(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
    }

    public function getAddress(PostContactType $type): ?AddressModel
    {
        $postContact = $this->getPostContact($type);
        return $postContact ? $postContact->address : null;
    }

    public function getPostContact(PostContactType $type): ?PostContactModel
    {
        /** @var PostContactModel|null $postContact */
        $postContact = $this->getPostContacts()->where(['type' => $type->value])->fetch();
        return $postContact;
    }

    public function getActivePostContact(): ?PostContactModel
    {
        return $this->getPostContact(PostContactType::from(PostContactType::PERMANENT)) ??
            $this->getPostContact(PostContactType::from(PostContactType::DELIVERY));
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventParticipantModel>
     */
    public function getEventParticipants(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamTeacherModel>
     */
    public function getFyziklaniTeachers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER, 'person_id');
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamMemberModel>
     */
    public function getTeamMembers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'person_id');
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventOrgModel>
     */
    public function getEventOrgs(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_ORG, 'person_id');
    }

    public function getFullName(): string
    {
        return $this->display_name ?? $this->other_name . ' ' . $this->family_name;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    /**
     * @phpstan-return OrgModel[] indexed by contest_id
     * @internal To get active orgs call FKSDB\Models\ORM\Models\ModelLogin::getActiveOrgs
     */
    public function getActiveOrgs(): array
    {
        $result = [];
        /** @var OrgModel $org */
        foreach ($this->getOrganisers() as $org) {
            $year = $org->contest->getCurrentContestYear()->year;
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->contest_id] = $org;
            }
        }
        return $result;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrgModel>
     */
    public function getOrganisers(?ContestModel $contest = null): TypedGroupedSelection
    {
        $related = $this->related(DbNames::TAB_ORG, 'person_id');
        if ($contest) {
            $related->where('contest_id', $contest->contest_id);
        }
        return $related;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrgModel>
     */
    public function getActiveOrgsAsQuery(ContestModel $contest): TypedGroupedSelection
    {
        $year = $contest->getCurrentContestYear()->year;
        return $this->getOrganisers($contest)
            ->where('since<=?', $year)
            ->where('until IS NULL OR until >=?', $year);
    }

    /**
     * @phpstan-return array{
     *     other_name:string,
     *     family_name:string,
     *     gender:string,
     * }
     */
    public static function parseFullName(string $fullName): array
    {
        $names = explode(' ', $fullName);
        $otherName = implode(' ', array_slice($names, 0, count($names) - 1));
        $familyName = $names[count($names) - 1];
        return [
            'other_name' => $otherName,
            'family_name' => $familyName,
            'gender' => self::inferGender(['family_name' => $familyName]),
        ];
    }

    /**
     * @phpstan-param array{family_name:string} $data
     */
    public static function inferGender(array $data): string
    {
        if (mb_substr($data['family_name'], -1) == 'á') {
            return 'F';
        } else {
            return 'M';
        }
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @phpstan-return array<int,int>
     */
    public function getSerializedSchedule(EventModel $event, string $type): array
    {
        $query = $this->getSchedule()
            ->where('schedule_item.schedule_group.event_id', $event->event_id)
            ->where('schedule_item.schedule_group.schedule_group_type', $type);
        $items = [];
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            $items[$model->schedule_item->schedule_group_id] = $model->schedule_item->schedule_item_id;
        }
        return $items;
    }

    /**
     * Definitely ugly but, there is only this way... Mišo
     * TODO refactoring
     */
    public function removeScheduleForEvent(EventModel $event): void
    {
        foreach ($this->getScheduleForEvent($event) as $row) {
            $row->delete();//@phpstan-ignore-line
        }
    }

    /**
     * @phpstan-return TypedGroupedSelection<PersonScheduleModel>
     */
    public function getScheduleForEvent(EventModel $event): TypedGroupedSelection
    {
        return $this->getSchedule()->where('schedule_item.schedule_group.event_id', $event->event_id);
    }

    public function getScheduleByGroup(ScheduleGroupModel $group): ?PersonScheduleModel
    {
        /** @var PersonScheduleModel|null $personSchedule */
        $personSchedule = $this->getSchedule()->where(
            'schedule_item.schedule_group_id',
            $group->schedule_group_id
        )->fetch();
        return $personSchedule;
    }

    public function getScheduleByItem(ScheduleItemModel $item): ?PersonScheduleModel
    {
        /** @var PersonScheduleModel|null $personSchedule */
        $personSchedule = $this->getSchedule()->where('schedule_item_id', $item->schedule_item_id)->fetch();
        return $personSchedule;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PersonScheduleModel>
     */
    public function getSchedule(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id');
    }

    /**
     * @param string[] $types
     * @phpstan-return PersonScheduleModel[]
     */
    public function getScheduleRests(
        EventModel $event,
        array $types = [
            ScheduleGroupType::ACCOMMODATION,
            ScheduleGroupType::WEEKEND,
        ]
    ): array {
        $toPay = [];
        $schedule = $this->getScheduleForEvent($event)
            ->where('schedule_item.schedule_group.schedule_group_type', $types)
            ->where('schedule_item.price_czk IS NOT NULL OR schedule_item.price_eur IS NOT NULL');
        /** @var PersonScheduleModel $pSchedule */
        foreach ($schedule as $pSchedule) {
            $payment = $pSchedule->getPayment();
            if (!$payment || $payment->state->value !== PaymentState::RECEIVED) {
                $toPay[] = $pSchedule;
            }
        }
        return $toPay;
    }

    /**
     * @return PersonGender|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'gender':
                $value = PersonGender::tryFrom($value);
                break;
        }
        return $value;
    }

    /**
     * @phpstan-return EventRole[]
     */
    public function getEventRoles(EventModel $event): array
    {
        $roles = [];
        $teachers = $this->getFyziklaniTeachers()->where('fyziklani_team.event_id', $event->event_id);
        if ($teachers->count('*')) {
            $teams = [];
            /** @var TeamTeacherModel $row */
            foreach ($teachers as $row) {
                $teams[] = $row->fyziklani_team;
            }
            $roles[] = new FyziklaniTeamTeacherRole($event, $teams);
        }
        /** @var EventOrgModel|null $eventOrg */
        $eventOrg = $this->getEventOrgs()->where('event_id', $event->event_id)->fetch();
        if (isset($eventOrg)) {
            $roles[] = new EventOrgRole($event, $eventOrg);
        }
        /** @var EventParticipantModel|null $eventParticipant */
        $eventParticipant = $this->getEventParticipants()->where('event_id', $event->event_id)->fetch();
        if (isset($eventParticipant)) {
            $roles[] = new ParticipantRole($event, $eventParticipant);
        }
        /** @var TeamMemberModel|null $teamMember */
        $teamMember = $this->getTeamMembers()->where('fyziklani_team.event_id', $event->event_id)->fetch();
        if ($teamMember) {
            $roles[] = new FyziklaniTeamMemberRole($event, $teamMember);
        }
        /** @var OrgModel|null $org */
        $org = $this->getActiveOrgsAsQuery($event->event_type->contest)->fetch();
        if (isset($org)) {
            $roles[] = new ContestOrgRole($event, $org);
        }
        return $roles;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskContributionModel>
     */
    public function getTaskContributions(?TaskContributionType $type = null): TypedGroupedSelection
    {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'person_id');
        if ($type) {
            $contributions->where('type', $type->value);
        }
        return $contributions;
    }
}
