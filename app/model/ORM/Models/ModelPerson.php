<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use ModelMPersonHasFlag;
use ModelMPostContact;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\Selection;
use Nette\Security\IResource;
use YearCalculator;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer person_id
 * @property string other_name
 * @property string family_name
 * @property string display_name
 * @property string gender
 */
class ModelPerson extends AbstractModelSingle implements IResource {
    /**
     * Returns first of the person's logins.
     * (so far, there's not support for multiple login in DB schema)
     *
     *
     */
    public function getLogin() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $logins = $this->related(DbNames::TAB_LOGIN, 'person_id');
        $logins->rewind();
        if (!$logins->valid()) {
            return null;
        }

        return ModelLogin::createFromTableRow($logins->current());
    }

    /**
     * @return ModelPersonInfo|null
     */
    public function getInfo() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }

        $infos = $this->related(DbNames::TAB_PERSON_INFO, 'person_id');
        $infos->rewind();
        if (!$infos->valid()) {
            return null;
        }

        return ModelPersonInfo::createFromTableRow($infos->current());
    }

    /**
     * @param $acYear
     * @param bool $extrapolated
     * @return ModelPersonHistory|null
     */
    public function getHistory($acYear, $extrapolated = false) {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $histories = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id')
            ->where('ac_year', $acYear);
        $history = $histories->fetch();
        if ($history) {
            return ModelPersonHistory::createFromTableRow($history);
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
     * @param null $contestId
     * @return \Nette\Database\Table\GroupedSelection
     */
    public function getContestants($contestId = null): GroupedSelection {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $related = $this->related(DbNames::TAB_CONTESTANT_BASE, 'person_id');
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    /**
     * @param null $contestId
     * @return \Nette\Database\Table\GroupedSelection
     */
    public function getOrgs($contestId = null): GroupedSelection {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $related = $this->related(DbNames::TAB_ORG, 'person_id');
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    /**
     * @return GroupedSelection
     */
    public function getFlags(): GroupedSelection {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_PERSON_HAS_FLAG, 'person_id');
    }

    /**
     * @return ModelMPersonHasFlag[]
     */
    public function getMPersonHasFlags() {
        $personFlags = $this->getFlags();

        if (!$personFlags || count($personFlags) == 0) {
            return null;
        }

        $result = [];
        foreach ($personFlags as $row) {
            $flag = $row->ref(DbNames::TAB_FLAG, 'flag_id');
            $result[] = ModelMPersonHasFlag::createFromExistingModels(
                ModelFlag::createFromTableRow($flag), ModelPersonHasFlag::createFromTableRow($row)
            );
        }
        return $result;
    }

    /**
     * @param $fid
     * @return ModelMPersonHasFlag|null
     */
    public function getMPersonHasFlag($fid) {
        $flags = $this->getMPersonHasFlags();

        if (!$flags || count($flags) == 0) {
            return null;
        }

        foreach ($flags as $flag) {
            if ($flag->getFlag()->fid == $fid) {
                return $flag;
            }
        }
        return null;
    }

    /**
     * @return GroupedSelection
     */
    public function getPostContacts() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
    }

    /**
     * @param null $type
     * @return array
     */
    public function getMPostContacts($type = null) {
        $postContacts = $this->getPostContacts();
        if ($postContacts && $type !== null) {
            $postContacts->where(['type' => $type]);
        }

        if (!$postContacts || count($postContacts) == 0) {
            return [];
        }

        $result = [];
        foreach ($postContacts as $postContact) {
            $postContact->address_id; // stupid touch
            $address = $postContact->ref(DbNames::TAB_ADDRESS, 'address_id');
            $result[] = ModelMPostContact::createFromExistingModels(
                ModelAddress::createFromTableRow($address), ModelPostContact::createFromTableRow($postContact)
            );
        }
        return $result;
    }

    /**
     * Main delivery address of the contestant.
     *
     * @return ModelPostContact|null
     */
    public function getDeliveryAddress() {
        $dAddresses = $this->getMPostContacts(ModelPostContact::TYPE_DELIVERY);
        if (count($dAddresses)) {
            return reset($dAddresses);
        } else {
            return null;
        }
    }

    /**
     * @param bool $noFallback
     * @return mixed|ModelPostContact|null
     */
    public function getPermanentAddress($noFallback = false) {
        $pAddresses = $this->getMPostContacts(ModelPostContact::TYPE_PERMANENT);
        if (count($pAddresses)) {
            return reset($pAddresses);
        } else if (!$noFallback) {
            return $this->getDeliveryAddress();
        } else {
            return null;
        }
    }

    /**
     * @return GroupedSelection
     */
    public function getEventParticipant(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    /**
     * @return GroupedSelection
     */
    public function getEventTeacher(): GroupedSelection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_TEAM, 'teacher_id');
    }

    /**
     * @param int|null $eventId
     * @return bool
     */
    public function isEventParticipant($eventId = null): bool {
        $tmp = $this->getEventParticipant();
        if ($eventId) {
            $tmp->where('event_id = ?', $eventId);
        }

        if ($tmp->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return GroupedSelection
     */
    public function getEventOrg() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_EVENT_ORG, 'person_id');
    }

    /**
     * @return null|ModelPersonHistory the most recent person's history record (if any)
     */
    private function getLastHistory() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $history = $this->related(DbNames::TAB_PERSON_HISTORY, 'person_id')->order(('ac_year DESC'))->fetch();

        if ($history) {
            return ModelPersonHistory::createFromTableRow($history);
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getFullName(): string {
        return $this->display_name ?: $this->other_name . ' ' . $this->family_name;
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->getFullName();
    }

    /**
     * @internal To get active orgs call FKSDB\ORM\Models\ModelLogin::getActiveOrgs
     * @param YearCalculator $yearCalculator
     * @return array of FKSDB\ORM\Models\ModelOrg indexed by contest_id
     */
    public function getActiveOrgs(YearCalculator $yearCalculator) {
        $result = [];
        foreach ($this->related(DbNames::TAB_ORG, 'person_id') as $org) {
            $org = ModelOrg::createFromTableRow($org);
            $year = $yearCalculator->getCurrentYear($org->getContest());
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->contest_id] = $org;
            }
        }
        return $result;
    }

    /**
     * Active contestant := contestant in the highest year but not older than the current year.
     *
     * @param YearCalculator $yearCalculator
     * @return array of FKSDB\ORM\Models\ModelContestant indexed by contest_id
     */
    public function getActiveContestants(YearCalculator $yearCalculator) {
        $result = [];
        foreach ($this->related(DbNames::TAB_CONTESTANT_BASE, 'person_id') as $contestant) {
            $contestant = ModelContestant::createFromTableRow($contestant);
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

    /**
     *
     * @param string $fullName
     * @return array
     */
    public static function parseFullName($fullName) {
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
    public function inferGender() {
        if (mb_substr($this->family_name, -1) == 'á') {
            $this->gender = 'F';
        } else {
            $this->gender = 'M';
        }
    }

    /*
     * IResource
     */
    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'person';
    }

    /**
     * @param integer eventId
     * @return string
     * @throws \Nette\Utils\JsonException
     */
    public function getSerializedAccommodationByEventId($eventId) {
        if (!$eventId) {
            return null;
        }

        $query = $this->related(DbNames::TAB_EVENT_PERSON_ACCOMMODATION, 'person_id')->where('event_accommodation.event_id=?', $eventId);
        $accommodations = [];
        foreach ($query as $row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $eventAcc = $model->getEventAccommodation();
            $key = $eventAcc->date->format(ModelEventAccommodation::ACC_DATE_FORMAT);
            $accommodations[$key] = $eventAcc->event_accommodation_id;
        }
        if (!count($accommodations)) {
            return null;
        }
        return \Nette\Utils\Json::encode($accommodations);
    }

    /**
     * @param $eventId
     * Definitely ugly but, there is only this way... Mišo
     */
    public function removeAccommodationForEvent($eventId) {
        $query = $this->related(DbNames::TAB_EVENT_PERSON_ACCOMMODATION, 'person_id')->where('event_accommodation.event_id=?', $eventId);
        /**
         * @var ModelEventPersonAccommodation $row
         */
        foreach ($query as $row) {
            $row->delete();
        }
    }

    /**
     * @return GroupedSelection
     */
    public function getPayments(): GroupedSelection {
        return $this->related(DbNames::TAB_PAYMENT, 'person_id');
    }

    /**
     * @param ModelEvent $event
     * @return Selection
     */
    public function getPaymentsForEvent(ModelEvent $event): Selection {
        return $this->getPayments()->where('event_id', $event->event_id);
    }

    /**
     * @param ModelEvent $event
     * @return Selection
     */
    public function getScheduleForEvent(ModelEvent $event): Selection {
        return $this->getSchedule()->where('group.event_id', $event->event_id);
    }

    /**
     * @return GroupedSelection
     */
    public function getSchedule(): GroupedSelection {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id');
    }

    /**
     * @param ModelEvent $event
     * @return array
     */
    public function getRolesForEvent(ModelEvent $event): array {
        $roles = [];
        $eventId = $event->event_id;
        $teachers = $this->getEventTeacher()->where('event_id', $eventId);
        foreach ($teachers as $row) {
            $team = ModelFyziklaniTeam::createFromTableRow($row);
            $roles[] = [
                'type' => 'teacher',
                'team' => $team,
            ];
        }
        $eventOrgs = $this->getEventOrg()->where('event_id', $eventId);
        foreach ($eventOrgs as $row) {
            $org = ModelEventOrg::createFromTableRow($row);
            $roles[] = [
                'type' => 'org',
                'org' => $org,
            ];
        }
        $eventParticipants = $this->getEventParticipant()->where('event_id', $eventId);
        foreach ($eventParticipants as $row) {
            $participant = ModelEventParticipant::createFromTableRow($row);
            $roles[] = [
                'type' => 'participant',
                'participant' => $participant,
            ];
        }
        return $roles;
    }

}

