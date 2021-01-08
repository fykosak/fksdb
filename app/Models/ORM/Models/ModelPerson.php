<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelSchedulePayment;
use FKSDB\Models\ORM\ModelsMulti\ModelMPostContact;
use FKSDB\Models\YearCalculator;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int person_id
 * @property-read string other_name
 * @property-read string family_name
 * @property-read string display_name
 * @property-read string gender
 * @property-read \DateTimeInterface created
 */
class ModelPerson extends OldAbstractModelSingle implements IResource {

    public const RESOURCE_ID = 'person';

    /**
     * Returns first of the person's logins.
     * (so far, there's not support for multiple login in DB schema)
     * @return ModelLogin|null
     */
    public function getLogin(): ?ModelLogin {
        $logins = $this->related(DbNames::TAB_LOGIN, 'person_id');
        $logins->rewind();
        if (!$logins->valid()) {
            return null;
        }

        return ModelLogin::createFromActiveRow($logins->current());
    }

    public function getPreferredLang(): ?string {
        return $this->getInfo() ? $this->getInfo()->preferred_lang : null;
    }

    public function getInfo(): ?ModelPersonInfo {
        $infos = $this->related(DbNames::TAB_PERSON_INFO, 'person_id');
        $infos->rewind();
        if (!$infos->valid()) {
            return null;
        }

        return ModelPersonInfo::createFromActiveRow($infos->current());
    }

    public function getHistory(int $acYear, bool $extrapolated = false): ?ModelPersonHistory {
        $history = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id')
            ->where('ac_year', $acYear)->fetch();
        if ($history) {
            return ModelPersonHistory::createFromActiveRow($history);
        }
        if ($extrapolated) {
            $lastHistory = $this->getLastHistory();
            if ($lastHistory) {
                return $lastHistory->extrapolate($acYear);
            } else {
                return null;
            }
        } else {
            return null;
        }
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

    /**
     * @return ModelPersonHasFlag[]
     */
    public function getPersonHasFlags(): array {
        $personFlags = $this->getFlags();
        $result = [];
        foreach ($personFlags as $row) {
            $result[] = ModelPersonHasFlag::createFromActiveRow($row);
        }
        return $result;
    }

    public function getPersonHasFlag(string $fid): ?ModelPersonHasFlag {
        $flags = $this->getPersonHasFlags();
        foreach ($flags as $flag) {
            if ($flag->getFlag()->fid === $fid) {
                return $flag;
            }
        }
        return null;
    }

    public function getPostContacts(): GroupedSelection {
        return $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
    }

    /**
     * @param string|null $type
     * @return ModelMPostContact[]
     */
    public function getMPostContacts(?string $type = null): array {
        $postContacts = $this->getPostContacts();
        if ($postContacts && $type !== null) {
            $postContacts->where(['type' => $type]);
        }

        if (!$postContacts || count($postContacts) == 0) {
            return [];
        }

        $result = [];
        foreach ($postContacts as $postContact) {
            $address = $postContact->ref(DbNames::TAB_ADDRESS, 'address_id');
            $result[] = ModelMPostContact::createFromExistingModels(
                ModelAddress::createFromActiveRow($address), ModelPostContact::createFromActiveRow($postContact)
            );
        }
        return $result;
    }

    /**
     * Main delivery address of the contestant.
     *
     * @return ModelMPostContact|null
     */
    public function getDeliveryAddress(): ?ModelMPostContact {
        $dAddresses = $this->getMPostContacts(ModelPostContact::TYPE_DELIVERY);
        if (count($dAddresses)) {
            return reset($dAddresses);
        } else {
            return null;
        }
    }

    public function getPermanentAddress(bool $noFallback = false): ?ModelMPostContact {
        $pAddresses = $this->getMPostContacts(ModelPostContact::TYPE_PERMANENT);
        if (count($pAddresses)) {
            return reset($pAddresses);
        } elseif (!$noFallback) {
            return $this->getDeliveryAddress();
        } else {
            return null;
        }
    }

    public function getDeliveryAddress2(): ?ModelAddress {
        return $this->getAddress2(ModelPostContact::TYPE_DELIVERY);
    }

    public function getPermanentAddress2(): ?ModelAddress {
        return $this->getAddress2(ModelPostContact::TYPE_PERMANENT);
    }

    public function getAddress2(string $type): ?ModelAddress {
        $postContact = $this->getPostContacts()->where(['type' => $type])->fetch();
        return $postContact ? ModelPostContact::createFromActiveRow($postContact)->getAddress() : null;
    }

    public function getEventParticipants(): GroupedSelection {
        //return (new Selection($this->getTable()->data,bNames::TAB_EVENT_PARTICIPANT, $this->getTable()->getConnection()))->where('person_id', $this->person_id);
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
        return ($tmp->count() > 0);
    }

    public function getEventOrgs(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT_ORG, 'person_id');
    }

    /**
     * @return null|ModelPersonHistory the most recent person's history record (if any)
     */
    private function getLastHistory(): ?ModelPersonHistory {
        $history = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id')->order(('ac_year DESC'))->fetch();

        if ($history) {
            return ModelPersonHistory::createFromActiveRow($history);
        } else {
            return null;
        }
    }

    public function getFullName(): string {
        return $this->display_name ?: $this->other_name . ' ' . $this->family_name;
    }

    public function __toString(): string {
        return $this->getFullName();
    }

    /**
     * @param YearCalculator $yearCalculator
     * @return ModelOrg[] indexed by contest_id
     * @internal To get active orgs call FKSDB\Models\ORM\Models\ModelLogin::getActiveOrgs
     */
    public function getActiveOrgs(YearCalculator $yearCalculator): array {
        $result = [];
        foreach ($this->related(DbNames::TAB_ORG, 'person_id') as $org) {
            $org = ModelOrg::createFromActiveRow($org);
            $year = $yearCalculator->getCurrentYear($org->getContest());
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->contest_id] = $org;
            }
        }
        return $result;
    }

    public function getActiveOrgsAsQuery(YearCalculator $yearCalculator, ModelContest $contest): GroupedSelection {
        $year = $yearCalculator->getCurrentYear($contest);
        return $this->related(DbNames::TAB_ORG, 'person_id')
            ->where('contest_id', $contest->contest_id)
            ->where('since<=?', $year)->where('until IS NULL OR until >=?', $year);
    }

    /**
     * Active contestant := contestant in the highest year but not older than the current year.
     *
     * @param YearCalculator $yearCalculator
     * @return ModelContestant[] indexed by contest_id
     */
    public function getActiveContestants(YearCalculator $yearCalculator): array {
        $result = [];
        foreach ($this->related(DbNames::TAB_CONTESTANT_BASE, 'person_id') as $contestant) {
            $contestant = ModelContestant::createFromActiveRow($contestant);
            $currentYear = $yearCalculator->getCurrentYear($contestant->getContest());
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

    /**
     * Infers gender from name.
     */
    public function inferGender(): void {
        if (mb_substr($this->family_name, -1) == 'á') {
            $this->gender = 'F';
        } else {
            $this->gender = 'M';
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
        /** @var ModelPersonSchedule $row */
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
    public function getScheduleRests(ModelEvent $event, array $types = ['accommodation', 'weekend']): array {
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
     * @param YearCalculator $yearCalculator
     * @return array[]
     */
    public function getRolesForEvent(ModelEvent $event, YearCalculator $yearCalculator): array {
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
        if (array_key_exists($event->getEventType()->contest_id, $this->getActiveOrgs($yearCalculator))) {
            $roles[] = [
                'type' => 'contest_org',
            ];
        }
        return $roles;
    }
}
