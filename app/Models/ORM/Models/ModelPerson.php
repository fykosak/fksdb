<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelSchedulePayment;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int person_id
 * @property-read string other_name
 * @property-read string family_name
 * @property-read string display_name
 * @property-read string gender
 * @property-read \DateTimeInterface created
 */
class ModelPerson extends AbstractModel implements Resource {

    public const RESOURCE_ID = 'person';

    /**
     * Returns first of the person's logins.
     * (so far, there's not support for multiple login in DB schema)
     * @return ModelLogin|null
     */
    public function getLogin(): ?ModelLogin {
        $login = $this->related(DbNames::TAB_LOGIN, 'person_id')->fetch();
        return $login ? ModelLogin::createFromActiveRow($login) : null;
    }

    public function getPreferredLang(): ?string {
        return $this->getInfo() ? $this->getInfo()->preferred_lang : null;
    }

    public function getInfo(): ?ModelPersonInfo {
        $info = $this->related(DbNames::TAB_PERSON_INFO, 'person_id')->fetch();
        return $info ? ModelPersonInfo::createFromActiveRow($info) : null;
    }

    public function getHistoryByContestYear(ModelContestYear $contestYear, bool $extrapolated = false): ?ModelPersonHistory {
        return $this->getHistory($contestYear->ac_year, $extrapolated);
    }

    public function getHistory(int $acYear, bool $extrapolated = false): ?ModelPersonHistory {
        $history = $this->related(DbNames::TAB_PERSON_HISTORY)
            ->where('ac_year', $acYear)
            ->fetch();
        if ($history) {
            return ModelPersonHistory::createFromActiveRow($history);
        }
        if ($extrapolated) {
            $lastHistory = $this->getLastHistory();
            return $lastHistory ? $lastHistory->extrapolate($acYear) : null;
        }
        return null;
    }

    /**
     * @param int|ModelContest|null $contest
     * @return GroupedSelection
     */
    public function getContestants($contest = null): GroupedSelection {
        $contestId = null;
        if ($contest instanceof ModelContest) {
            $contestId = $contest->contest_id;
        } else {
            $contestId = $contest;
        }
        $related = $this->related(DbNames::TAB_CONTESTANT_BASE, 'person_id');
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    public function getOrgs(?int $contestId = null): GroupedSelection {
        $related = $this->related(DbNames::TAB_ORG, 'person_id');
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    public function getFlags(): GroupedSelection {
        return $this->related(DbNames::TAB_PERSON_HAS_FLAG, 'person_id');
    }

    public function getPersonHasFlag(string $flagType): ?ModelPersonHasFlag {
        $row = $this->getFlags()->where('flag.fid', $flagType)->fetch();
        return $row ? ModelPersonHasFlag::createFromActiveRow($row) : null;
    }

    public function getPostContacts(): GroupedSelection {
        return $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
    }

    public function getDeliveryAddress(): ?ModelAddress {
        return $this->getAddress(ModelPostContact::TYPE_DELIVERY);
    }

    public function getPermanentAddress(): ?ModelAddress {
        return $this->getAddress(ModelPostContact::TYPE_PERMANENT);
    }

    public function getAddress(string $type): ?ModelAddress {
        $postContact = $this->getPostContact($type);
        return $postContact ? $postContact->getAddress() : null;
    }

    public function getPostContact(string $type): ?ModelPostContact {
        $postContact = $this->getPostContacts()->where(['type' => $type])->fetch();
        return $postContact ? ModelPostContact::createFromActiveRow($postContact) : null;
    }

    public function getDeliveryPostContact(): ?ModelPostContact {
        return $this->getPostContact(ModelPostContact::TYPE_DELIVERY);
    }

    public function getPermanentPostContact(bool $noFallback = false): ?ModelPostContact {
        $postContact = $this->getPostContact(ModelPostContact::TYPE_PERMANENT);
        if ($postContact) {
            return $postContact;
        } elseif (!$noFallback) {
            return $this->getDeliveryPostContact();
        } else {
            return null;
        }
    }

    public function getEventParticipants(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    public function getEventTeachers(): GroupedSelection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_TEAM, 'teacher_id');
    }

    public function isEventParticipant(?int $eventId = null): bool {
        $tmp = $this->getEventParticipants();
        if ($eventId) {
            $tmp->where('event_id = ?', $eventId);
        }
        return (bool)$tmp->fetch();
    }

    public function getEventOrgs(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT_ORG, 'person_id');
    }

    /**
     * @return null|ModelPersonHistory the most recent person's history record (if any)
     */
    private function getLastHistory(): ?ModelPersonHistory {
        $row = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id')->order(('ac_year DESC'))->fetch();
        return $row ? ModelPersonHistory::createFromActiveRow($row) : null;
    }

    public function getFullName(): string {
        return $this->display_name ?? $this->other_name . ' ' . $this->family_name;
    }

    public function __toString(): string {
        return $this->getFullName();
    }

    /**
     * @return ModelOrg[] indexed by contest_id
     * @internal To get active orgs call FKSDB\Models\ORM\Models\ModelLogin::getActiveOrgs
     */
    public function getActiveOrgs(): array {
        $result = [];
        foreach ($this->related(DbNames::TAB_ORG, 'person_id') as $org) {
            $org = ModelOrg::createFromActiveRow($org);
            $year = $org->getContest()->getCurrentContestYear()->year;
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->contest_id] = $org;
            }
        }
        return $result;
    }

    public function getActiveOrgsAsQuery(ModelContest $contest): GroupedSelection {
        $year = $contest->getCurrentContestYear()->year;
        return $this->related(DbNames::TAB_ORG, 'person_id')
            ->where('contest_id', $contest->contest_id)
            ->where('since<=?', $year)
            ->where('until IS NULL OR until >=?', $year);
    }

    /**
     * Active contestant := contestant in the highest year but not older than the current year.
     *
     * @return ModelContestant[] indexed by contest_id
     */
    public function getActiveContestants(): array {
        $result = [];
        foreach ($this->related(DbNames::TAB_CONTESTANT_BASE, 'person_id') as $contestant) {
            $contestant = ModelContestant::createFromActiveRow($contestant);
            $currentYear = $contestant->getContest()->getCurrentContestYear()->year;
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

    public static function parseFullName(string $fullName): array {
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

    public static function inferGender(array $data): string {
        if (mb_substr($data['family_name'], -1) == 'á') {
            return 'F';
        } else {
            return 'M';
        }
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

    /**
     * @param int eventId
     * @param string $type
     * @return string|null
     */
    public function getSerializedSchedule(int $eventId, string $type): ?string {
        if (!$eventId) {
            return null;
        }
        $query = $this->getSchedule()
            ->where('schedule_item.schedule_group.event_id', $eventId)
            ->where('schedule_item.schedule_group.schedule_group_type', $type);
        $items = [];
        foreach ($query as $row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            $scheduleItem = $model->getScheduleItem();
            $items[$scheduleItem->schedule_group_id] = $scheduleItem->schedule_item_id;
        }
        if (!count($items)) {
            return null;
        }

        return json_encode($items);
    }

    /**
     * @param int $eventId
     * Definitely ugly but, there is only this way... Mišo
     * TODO refactoring
     */
    public function removeScheduleForEvent(int $eventId): void {
        $query = $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id')->where('schedule_item.schedule_group.event_id=?', $eventId);
        foreach ($query as $row) {
            $row->delete();
        }
    }

    public function getScheduleForEvent(ModelEvent $event): GroupedSelection {
        return $this->getSchedule()->where('schedule_item.schedule_group.event_id', $event->event_id);
    }

    public function getSchedule(): GroupedSelection {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id');
    }

    /**
     * @param ModelEvent $event
     * @param string[] $types
     * @return ModelSchedulePayment[]
     */
    public function getScheduleRests(ModelEvent $event, array $types = [ModelScheduleGroup::TYPE_ACCOMMODATION, ModelScheduleGroup::TYPE_WEEKEND]): array {
        $toPay = [];
        $schedule = $this->getScheduleForEvent($event)
            ->where('schedule_item.schedule_group.schedule_group_type', $types)
            ->where('schedule_item.price_czk IS NOT NULL');
        foreach ($schedule as $pSchRow) {
            $pSchedule = ModelPersonSchedule::createFromActiveRow($pSchRow);
            $payment = $pSchedule->getPayment();
            if (!$payment || $payment->state !== ModelPayment::STATE_RECEIVED) {
                $toPay[] = $pSchedule;
            }
        }
        return $toPay;
    }

    /**
     * @param ModelEvent $event
     * @return array[]
     */
    public function getRolesForEvent(ModelEvent $event): array {
        $roles = [];
        $eventId = $event->event_id;
        $teachers = $this->getEventTeachers()->where('event_id', $eventId);
        foreach ($teachers as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $roles[] = [
                'type' => 'teacher',
                'team' => $team,
            ];
        }
        $eventOrgs = $this->getEventOrgs()->where('event_id', $eventId);
        foreach ($eventOrgs as $row) {
            $org = ModelEventOrg::createFromActiveRow($row);
            $roles[] = [
                'type' => 'org',
                'org' => $org,
            ];
        }
        $eventParticipants = $this->getEventParticipants()->where('event_id', $eventId);
        foreach ($eventParticipants as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $roles[] = [
                'type' => 'participant',
                'participant' => $participant,
            ];
        }
        if ($this->getActiveOrgsAsQuery($event->getEventType()->getContest())->fetch()) {
            $roles[] = [
                'type' => 'contest_org',
            ];
        }
        return $roles;
    }
}
