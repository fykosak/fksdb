<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\EventRole\{ContestOrgRole,
    EventOrgRole,
    FyziklaniTeamMemberRole,
    FyziklaniTeamTeacherRole,
    ParticipantRole
};
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\Utils\FakeStringEnum;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

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

    public function getPreferredLang(): ?Language
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

    public function getPermanentPostContact(bool $fallback = true): ?PostContactModel
    {
        $postContact = $this->getPostContact(PostContactType::tryFrom(PostContactType::PERMANENT));
        if ($postContact) {
            return $postContact;
        } elseif ($fallback) {
            return $this->getPostContact(PostContactType::tryFrom(PostContactType::DELIVERY));
        } else {
            return null;
        }
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

    /**
     * @deprecated
     */
    public function getEventOrgs(): TypedGroupedSelection
    {
        return $this->getEventOrganisers();
    }

    public function getEventOrganisers(): TypedGroupedSelection
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
     */
    public function getActiveOrganisers(): array
    {
        $result = [];
        /** @var OrgModel $organiser */
        foreach ($this->getOrganisers() as $organiser) {
            $year = $organiser->contest->getCurrentContestYear()->year;
            if ($organiser->since <= $year && ($organiser->until === null || $organiser->until >= $year)) {
                $result[$organiser->contest_id] = $organiser;
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

    public function getActiveOrganisersAsQuery(ContestModel $contest): TypedGroupedSelection
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

    public function getSerializedSchedule(EventModel $event, string $type): ?string
    {
        $query = $this->getSchedule()
            ->where('schedule_item.schedule_group.event_id', $event->event_id)
            ->where('schedule_item.schedule_group.schedule_group_type', $type);
        $items = [];
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            $scheduleItem = $model->schedule_item;
            $items[$scheduleItem->schedule_group_id] = $scheduleItem->schedule_item_id;
        }
        if (!count($items)) {
            return null;
        }

        return json_encode($items);
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

    public function getSchedule(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id');
    }

    /**
     * @param string[] $types
     * @return SchedulePaymentModel[]
     * @throws \Exception
     */
    public function getScheduleRests(
        EventModel $event,
        array $types = [ScheduleGroupType::ACCOMMODATION, ScheduleGroupType::WEEKEND]
    ): array {
        $toPay = [];
        /** @var PersonScheduleModel $pSchedule */
        foreach ($this->getScheduleForEvent($event) as $pSchedule) {
            if (
                $pSchedule->schedule_item->isPayable()
                && in_array($pSchedule->schedule_item->schedule_group->schedule_group_type->value, $types)
            ) {
                $payment = $pSchedule->getPayment();
                if (!$payment || $payment->state->value !== PaymentState::RECEIVED) {
                    $toPay[] = $pSchedule;
                }
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
        /** @var EventOrgModel $eventOrganiser */
        $eventOrganiser = $this->getEventOrganisers()->where('event_id', $event->event_id)->fetch();
        if (isset($eventOrganiser)) {
            $roles[] = new EventOrgRole($event, $eventOrganiser);
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
        /** @var OrgModel $organiser */
        $organiser = $this->getActiveOrganisersAsQuery($event->event_type->contest)->fetch();
        if (isset($organiser)) {
            $roles[] = new ContestOrgRole($event, $organiser);
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
