<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\EventRole\{ContestOrgRole,
    EventOrgRole,
    FyziklaniTeamTeacherRole,
    FyziklaniTeamMemberRole,
    ParticipantRole
};
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;
use Tracy\Debugger;

/**
 * @property-read int person_id
 * @property-read string family_name
 * @property-read string other_name
 * @property-read string born_family_name
 * @property-read string display_name
 * @property-read PersonGender gender
 * @property-read \DateTimeInterface created
 */
class PersonModel extends Model implements Resource
{

    public const RESOURCE_ID = 'person';

    /**
     * Returns first of the person's logins.
     * (so far, there's not support for multiple login in DB schema)
     */
    public function getLogin(): ?LoginModel
    {
        return $this->related(DbNames::TAB_LOGIN, 'person_id')->fetch();
    }

    public function getPreferredLang(): ?string
    {
        return $this->getInfo() ? $this->getInfo()->preferred_lang : null;
    }

    public function getInfo(): ?PersonInfoModel
    {
        return $this->related(DbNames::TAB_PERSON_INFO, 'person_id')->fetch();
    }

    public function getHistoryByContestYear(
        ContestYearModel $contestYear,
        bool $extrapolated = false
    ): ?PersonHistoryModel {
        return $this->getHistory($contestYear->ac_year, $extrapolated);
    }

    public function getHistories(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id');
    }

    public function getHistory(int $acYear, bool $extrapolated = false): ?PersonHistoryModel
    {
        $history = $this->getHistories()
            ->where('ac_year', $acYear)
            ->fetch();
        if ($history) {
            return $history;
        }
        if ($extrapolated) {
            $lastHistory = $this->getLastHistory();
            return $lastHistory ? $lastHistory->extrapolate($acYear) : null;
        }
        return null;
    }

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
        return $this->getContestants($contestYear->contest)->where('year', $contestYear->year)->fetch();
    }

    /**
     * @deprecated
     */
    public function getOrgs(?int $contestId = null): TypedGroupedSelection
    {
        $related = $this->getOrganisers();
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    public function getFlags(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_HAS_FLAG, 'person_id');
    }

    public function hasPersonFlag(string $flagType): ?PersonHasFlagModel
    {
        return $this->getFlags()->where('flag.fid', $flagType)->fetch();
    }

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
        return $this->getPostContacts()->where(['type' => $type->value])->fetch();
    }

    public function getActivePostContact(): ?PostContactModel
    {
        return $this->getPostContact(PostContactType::tryFrom(PostContactType::PERMANENT)) ??
            $this->getPostContact(PostContactType::tryFrom(PostContactType::DELIVERY));
    }

    public function getEventParticipants(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    public function getFyziklaniTeachers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER, 'person_id');
    }

    public function getTeamMembers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'person_id');
    }

    public function getEventOrgs(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_ORG, 'person_id');
    }

    /**
     * @return null|PersonHistoryModel the most recent person's history record (if any)
     */
    private function getLastHistory(): ?PersonHistoryModel
    {
        return $this->getHistories()->order(('ac_year DESC'))->fetch();
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
     * @return OrgModel[] indexed by contest_id
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

    public function getOrganisers(?ContestModel $contest = null): TypedGroupedSelection
    {
        $related = $this->related(DbNames::TAB_ORG, 'person_id');
        if ($contest) {
            $related->where('contest_id', $contest->contest_id);
        }
        return $related;
    }

    public function getActiveOrgsAsQuery(ContestModel $contest): TypedGroupedSelection
    {
        $year = $contest->getCurrentContestYear()->year;
        return $this->getOrganisers($contest)
            ->where('since<=?', $year)
            ->where('until IS NULL OR until >=?', $year);
    }

    public static function parseFullName(string $fullName): array
    {
        $names = explode(' ', $fullName);
        $otherName = implode(' ', array_slice($names, 0, count($names) - 1));
        $familyName = $names[count($names) - 1];
        if (mb_substr($familyName, -1) == 'á') {
            $gender = 'F';
        } else {
            $gender = 'M';
        }
        return [
            'other_name' => $otherName,
            'family_name' => $familyName,
            'gender' => $gender,
        ];
    }

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
            $row->delete();
        }
    }

    public function getScheduleForEvent(EventModel $event): TypedGroupedSelection
    {
        return $this->getSchedule()->where('schedule_item.schedule_group.event_id', $event->event_id);
    }

    public function getScheduleByGroup(ScheduleGroupModel $group): ?PersonScheduleModel
    {
        return $this->getSchedule()->where('schedule_item.schedule_group_id', $group->schedule_group_id)->fetch();
    }

    public function getSchedule(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id');
    }

    /**
     * @param string[] $types
     * @return SchedulePaymentModel[]
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
     * @return PersonGender|FakeStringEnum|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'gender':
                $value = PersonGender::tryFrom($value);
                break;
        }
        return $value;
    }

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
        /** @var EventOrgModel $eventOrg */
        $eventOrg = $this->getEventOrgs()->where('event_id', $event->event_id)->fetch();
        if (isset($eventOrg)) {
            $roles[] = new EventOrgRole($event, $eventOrg);
        }
        /** @var EventParticipantModel $eventParticipant */
        $eventParticipant = $this->getEventParticipants()->where('event_id', $event->event_id)->fetch();
        if (isset($eventParticipant)) {
            $roles[] = new ParticipantRole(
                $event,
                $eventParticipant
            );
        }
        /** @var TeamMemberModel $teamMember */
        $teamMember = $this->getTeamMembers()->where('fyziklani_team.event_id', $event->event_id)->fetch();
        if ($teamMember) {
            $roles[] = new FyziklaniTeamMemberRole(
                $event,
                $teamMember
            );
        }
        /** @var OrgModel $org */
        $org = $this->getActiveOrgsAsQuery($event->event_type->contest)->fetch();
        if (isset($org)) {
            $roles[] = new ContestOrgRole($event, $org);
        }
        return $roles;
    }

    public function getTaskContributions(?TaskContributionType $type = null): TypedGroupedSelection
    {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'person_id');
        if ($type) {
            $contributions->where('type', $type->value);
        }
        return $contributions;
    }
}
