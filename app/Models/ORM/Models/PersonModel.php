<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\EventRole\{ContestOrganizerRole,
    EventOrganizerRole,
    EventRole,
    FyziklaniTeamMemberRole,
    FyziklaniTeamTeacherRole,
    ParticipantRole
};
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
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
 * @phpstan-type TSimplePersonArray array{personId:int,name:string,email:string|null}
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
        /** @phpstan-var TypedGroupedSelection<PersonHistoryModel> $selection */
        $selection = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id');
        return $selection;
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
        /** @phpstan-var TypedGroupedSelection<ContestantModel> $related */
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
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    public function getLegacyOrganizers(?int $contestId = null): TypedGroupedSelection
    {
        $related = $this->getOrganizers();
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
        /** @phpstan-var TypedGroupedSelection<PersonHasFlagModel> $selection */
        $selection = $this->related(DbNames::TAB_PERSON_HAS_FLAG, 'person_id');
        return $selection;
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
        /** @phpstan-var TypedGroupedSelection<PostContactModel> $selection */
        $selection = $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
        return $selection;
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
        /** @phpstan-var TypedGroupedSelection<EventParticipantModel> $selection */
        $selection = $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamTeacherModel>
     */
    public function getTeamTeachers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamTeacherModel> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER, 'person_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamMemberModel>
     */
    public function getTeamMembers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamMemberModel> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'person_id');
        return $selection;
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws NotFoundException
     */
    public function getApplication(EventModel $event): Model
    {
        /** @var TeamMemberModel|null $member */
        $member = $this->getTeamMembers()->where('fyziklani_team.event_id', $event->event_id)->fetch();
        if ($member) {
            return $member->fyziklani_team;
        }
        /** @var TeamTeacherModel|null $teacher */
        $teacher = $this->getTeamTeachers()->where('fyziklani_team.event_id', $event->event_id)->fetch();
        if ($teacher) {
            return $teacher->fyziklani_team;
        }
        /** @var EventParticipantModel|null $participant */
        $participant = $this->getEventParticipants()->where('event_id', $event->event_id)->fetch();
        if ($participant) {
            return $participant;
        }
        throw new NotFoundException();
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventOrganizerModel>
     */
    public function getEventOrganizers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventOrganizerModel> $selection */
        $selection = $this->related(DbNames::TAB_EVENT_ORGANIZER, 'person_id');
        return $selection;
    }

    public function getFullName(): string
    {
        return $this->display_name ?? $this->other_name . ' ' . $this->family_name;
    }

    /**
     * @phpstan-return OrganizerModel[] indexed by contest_id
     */
    public function getActiveOrganizers(): array
    {
        $result = [];
        /** @var OrganizerModel $organizer */
        foreach ($this->getOrganizers() as $organizer) {
            $year = $organizer->contest->getCurrentContestYear()->year;
            if ($organizer->since <= $year && ($organizer->until === null || $organizer->until >= $year)) {
                $result[$organizer->contest_id] = $organizer;
            }
        }
        return $result;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    public function getOrganizers(?ContestModel $contest = null): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<OrganizerModel> $selection */
        $selection = $this->related(DbNames::TAB_ORGANIZER, 'person_id');
        if ($contest) {
            $selection->where('contest_id', $contest->contest_id);
        }
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    public function getActiveOrganizersAsQuery(ContestModel $contest): TypedGroupedSelection
    {
        $year = $contest->getCurrentContestYear()->year;
        return $this->getOrganizers($contest)
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
        /** @phpstan-var TypedGroupedSelection<PersonScheduleModel> $selection */
        $selection = $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id');
        return $selection;
    }

    /**
     * @phpstan-param string[] $types
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
        $teachers = $this->getTeamTeachers()->where('fyziklani_team.event_id', $event->event_id);
        if ($teachers->count('*')) {
            $teams = [];
            /** @var TeamTeacherModel $row */
            foreach ($teachers as $row) {
                $teams[] = $row->fyziklani_team;
            }
            $roles[] = new FyziklaniTeamTeacherRole($event, $teams);
        }
        /** @var EventOrganizerModel|null $eventOrganizer */
        $eventOrganizer = $this->getEventOrganizers()->where('event_id', $event->event_id)->fetch();
        if (isset($eventOrganizer)) {
            $roles[] = new EventOrganizerRole($event, $eventOrganizer);
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
        /** @var OrganizerModel|null $organizer */
        $organizer = $this->getActiveOrganizersAsQuery($event->event_type->contest)->fetch();
        if (isset($organizer)) {
            $roles[] = new ContestOrganizerRole($event, $organizer);
        }
        return $roles;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskContributionModel>
     */
    public function getTaskContributions(?TaskContributionType $type = null): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TaskContributionModel> $selection */
        $selection = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'person_id');
        if ($type) {
            $selection->where('type', $type->value);
        }
        return $selection;
    }

    /**
     * @phpstan-return TSimplePersonArray
     */
    public function __toArray(): array
    {
        return [
            'name' => $this->getFullName(),
            'personId' => $this->person_id,
            'email' => $this->getInfo()->email,
        ];
    }
}
