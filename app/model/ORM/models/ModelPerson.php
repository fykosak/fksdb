<?php

use Nette\Security\IResource;

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
     * @return ModelLogin|null
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
    public function getContestants($contestId = null) {
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
    public function getOrgs($contestId = null) {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $related = $this->related(DbNames::TAB_ORG, 'person_id');
        if ($contestId) {
            $related->where('contest_id', $contestId);
        }
        return $related;
    }

    public function getFlags() {
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
        foreach ($personFlags as $personFlag) {
            $personFlag->flag_id; // stupid touch
            $flag = $personFlag->ref(DbNames::TAB_FLAG, 'flag_id');
            $result[] = ModelMPersonHasFlag::createFromExistingModels(
                ModelFlag::createFromTableRow($flag), ModelPersonHasFlag::createFromTableRow($personFlag)
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

    public function getEventParticipant() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    public function isEventParticipant($event_id = null) {
        $tmp = $this->getEventParticipant();
        if ($event_id) {
            $tmp->where('action_id = ?', $event_id);
        }

        if ($tmp->count() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

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

    public function getFullName() {
        return $this->display_name ?: $this->other_name . ' ' . $this->family_name;
    }

    public function __toString() {
        return $this->getFullName();
    }

    /**
     * @internal To get active orgs call ModelLogin::getActiveOrgs
     * @param YearCalculator $yearCalculator
     * @return array of ModelOrg indexed by contest_id
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
     * @return array of ModelContestant indexed by contest_id
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
     * @param string $fullname
     * @return array
     */
    public static function parseFullName($fullname) {
        $names = explode(' ', $fullname);
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
            return 'F';
        } else {
            return 'M';
        }
    }

    /*
     * IResource
     */

    public function getResourceId() {
        return 'person';
    }

    /**
     * @param integer eventId
     * @return string
     * @throws \Nette\Utils\JsonException
     */
    public function getAccommodationByEventId($eventId) {
        $query = $this->related(DbNames::TAB_EVENT_PERSON_ACCOMMODATION, 'person_id')->where('event_accommodation.event_id=?', $eventId);
        $accommodations = [];
        foreach ($query as $row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            /**
             * @var ModelEventAccommodation $eventAcc
             */
            $eventAcc = $model->getEventAccommodation();
            $key = $eventAcc->date->format(ModelEventAccommodation::ACC_DATE_FORMAT);
            $accommodations[$key] = $eventAcc->event_accommodation_id;
        }
        return \Nette\Utils\Json::encode($accommodations);
    }

}

