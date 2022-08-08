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
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
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
        $login = $this->related(DbNames::TAB_LOGIN, 'person_id')->fetch();
        return $login ? LoginModel::createFromActiveRow($login) : null;
    }

    public function getPreferredLang(): ?string
    {
        return $this->getInfo() ? $this->getInfo()->preferred_lang : null;
    }

    public function getInfo(): ?PersonInfoModel
    {
        $info = $this->related(DbNames::TAB_PERSON_INFO, 'person_id')->fetch();
        return $info ? PersonInfoModel::createFromActiveRow($info) : null;
    }

    public function getHistoryByContestYear(
        ContestYearModel $contestYear,
        bool $extrapolated = false
    ): ?PersonHistoryModel {
        return $this->getHistory($contestYear->ac_year, $extrapolated);
    }

    public function getHistory(int $acYear, bool $extrapolated = false): ?PersonHistoryModel
    {
        $history = $this->related(DbNames::TAB_PERSON_HISTORY)
            ->where('ac_year', $acYear)
            ->fetch();
        if ($history) {
            return PersonHistoryModel::createFromActiveRow($history);
        }
        if ($extrapolated) {
            $lastHistory = $this->getLastHistory();
            return $lastHistory ? $lastHistory->extrapolate($acYear) : null;
        }
        return null;
    }

    public function getContestants(?ContestModel $contest = null): GroupedSelection
    {
        $related = $this->related(DbNames::TAB_CONTESTANT, 'person_id');
        if ($contest) {
            $related->where('contest_id', $contest->contest_id);
        }
        return $related;
    }

    public function getOrgs(?int $contestId = null): GroupedSelection
    {
        $related = $this->related(DbNames::TAB_ORG, 'person_id');
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    public function getFlags(): GroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_HAS_FLAG, 'person_id');
    }

    public function hasPersonFlag(string $flagType): ?PersonHasFlagModel
    {
        $row = $this->getFlags()->where('flag.fid', $flagType)->fetch();
        return $row ? PersonHasFlagModel::createFromActiveRow($row) : null;
    }

    public function getPostContacts(): GroupedSelection
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
        $postContact = $this->getPostContacts()->where(['type' => $type->value])->fetch();
        return $postContact ? PostContactModel::createFromActiveRow($postContact) : null;
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

    public function getEventParticipants(): GroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    public function getFyziklaniTeachers(): GroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER, 'person_id');
    }

    public function getTeamMembers(): GroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'person_id');
    }

    public function getEventOrgs(): GroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_ORG, 'person_id');
    }

    /**
     * @return null|PersonHistoryModel the most recent person's history record (if any)
     */
    private function getLastHistory(): ?PersonHistoryModel
    {
        $row = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id')->order(('ac_year DESC'))->fetch();
        return $row ? PersonHistoryModel::createFromActiveRow($row) : null;
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
        foreach ($this->related(DbNames::TAB_ORG, 'person_id') as $org) {
            $org = OrgModel::createFromActiveRow($org);
            $year = $org->contest->getCurrentContestYear()->year;
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->contest_id] = $org;
            }
        }
        return $result;
    }

    public function getActiveOrgsAsQuery(ContestModel $contest): GroupedSelection
    {
        $year = $contest->getCurrentContestYear()->year;
        return $this->related(DbNames::TAB_ORG, 'person_id')
            ->where('contest_id', $contest->contest_id)
            ->where('since<=?', $year)
            ->where('until IS NULL OR until >=?', $year);
    }

    /**
     * Active contestant := contestant in the highest year but not older than the current year.
     *
     * @return ContestantModel[] indexed by contest_id
     */
    public function getActiveContestants(): array
    {
        $result = [];
        foreach ($this->related(DbNames::TAB_CONTESTANT, 'person_id') as $contestant) {
            $contestant = ContestantModel::createFromActiveRow($contestant);
            $currentYear = $contestant->contest->getCurrentContestYear()->year;
            if ($contestant->year >= $currentYear) { // forward contestant
                if (isset($result[$contestant->contest_id])) {
                    if ($contestant->year > $result[$contestant->contest_id]->year) {
                        $result[$contestant->contest_id] = $contestant;
                    }
                } else {
                    $result[$contestant->contest_id] = $contestant;
                }
            }
        }
        return $result;
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
        foreach ($query as $row) {
            $model = PersonScheduleModel::createFromActiveRow($row);
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
        $query = $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id')->where(
            'schedule_item.schedule_group.event_id=?',
            $event->event_id
        );
        foreach ($query as $row) {
            $row->delete();
        }
    }

    public function getScheduleForEvent(EventModel $event): GroupedSelection
    {
        return $this->getSchedule()->where('schedule_item.schedule_group.event_id', $event->event_id);
    }

    public function getSchedule(): GroupedSelection
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
        foreach ($schedule as $pSchRow) {
            $pSchedule = PersonScheduleModel::createFromActiveRow($pSchRow);
            $payment = $pSchedule->getPayment();
            if (!$payment || $payment->state->value !== PaymentState::RECEIVED) {
                $toPay[] = $pSchedule;
            }
        }
        return $toPay;
    }

    /**
     * @return PersonGender|FakeStringEnum|mixed|ActiveRow|null
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

        $eventId = $event->event_id;
        $teachers = $this->getFyziklaniTeachers()->where('fyziklani_team.event_id', $eventId);
        if ($teachers->count('*')) {
            $teams = [];
            foreach ($teachers as $row) {
                $teams[] = TeamTeacherModel::createFromActiveRow($row)->fyziklani_team;
            }
            $roles[] = new FyziklaniTeamTeacherRole($event, $teams);
        }

        $eventOrg = $this->getEventOrgs()->where('event_id', $eventId)->fetch();
        if (isset($eventOrg)) {
            $roles[] = new EventOrgRole($event, EventOrgModel::createFromActiveRow($eventOrg));
        }
        $eventParticipant = $this->getEventParticipants()->where('event_id', $eventId)->fetch();
        if (isset($eventParticipant)) {
            $roles[] = new ParticipantRole(
                $event,
                EventParticipantModel::createFromActiveRow($eventParticipant)
            );
        }
        $teamMember = $this->getTeamMembers()->where('fyziklani_team.event_id', $eventId)->fetch();
        if ($teamMember) {
            $roles[] = new FyziklaniTeamMemberRole(
                $event,
                TeamMemberModel::createFromActiveRow($teamMember)
            );
        }
        $org = $this->getActiveOrgsAsQuery($event->getContest())->fetch();
        if (isset($org)) {
            $roles[] = new ContestOrgRole($event, OrgModel::createFromActiveRow($org));
        }
        return $roles;
    }
}
