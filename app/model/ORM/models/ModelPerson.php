<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPerson extends AbstractModelSingle implements IResource {

    /**
     * Returns first of the person's logins.
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

    public function getContestants() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_CONTESTANT, 'person_id');
    }

    public function getPostContacts() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
    }

    /**
     * @return array of MPostContact
     */
    public function getMPostContacts($type = null) {
        $postContacts = $this->getPostContacts();
        if ($postContacts && $type !== null) {
            $postContacts->where(array('type' => $type));
        }

        if (!$postContacts || count($postContacts) == 0) {
            return array();
        }

        $result = array();
        foreach ($postContacts as $postContact) {
            $postContact->address_id; // stupid touch
            $address = $postContact->ref(DbNames::TAB_ADDRESS, 'address_id');
            $result[] = new ModelMPostContact(
                    ModelAddress::createFromTableRow($address), ModelPostContact::createFromTableRow($postContact)
            );
        }
        return $result;
    }

    public function getEventParticipant() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'person_id');
    }

    public function isEventParticipant($event_id = null) {
        $tmp = $this->getEventParticipant();
        if ($action_id) {
            $tmp->where('action_id = ?', $event_id);
        }

        if ($tmp->count() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 
     * @param ModelContest $contest
     * @return null|ModelContestant the most recent contestant for the person and given contest (if any)
     */
    public function getLastContestant(ModelContest $contest) {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        $contestant = $this->getContestants()->where('contest_id = ?', $contest->contest_id)->order('year DESC')->fetch();

        if ($contestant) {
            return ModelContestant::createFromTableRow($contestant);
        } else {
            return null;
        }
    }

    public function getFullname() {
        return $this->display_name ? : $this->other_name . ' ' . $this->family_name;
    }

    /**
     * 
     * @param YearCalculator $yearCalculator
     * @return array of ModelOrg indexed by org_id
     */
    public function getActiveOrgs(YearCalculator $yearCalculator) {
        $result = array();
        foreach ($this->related(DbNames::TAB_ORG, 'person_id') as $org) {
            $org = ModelOrg::createFromTableRow($org);
            $year = $yearCalculator->getCurrentYear($org->getContest());
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->org_id] = $org;
            }
        }
        return $result;
    }

    /**
     * 
     * @param YearCalculator $yearCalculator
     * @return array of ModelContestant indexed by ct_id
     */
    public function getActiveContestants(YearCalculator $yearCalculator) {
        $result = array();
        foreach ($this->related(DbNames::TAB_CONTESTANT, 'person_id') as $contestant) {
            $contestant = ModelContestant::createFromTableRow($contestant);
            $year = $yearCalculator->getCurrentYear($contestant->getContest());
            if ($contestant->year == $year) {
                $result[$contestant->ct_id] = $contestant;
            }
        }
        return $result;
    }

    /**
     * 
     * @param str $fullname
     * @return array
     */
    public static function parseFullname($fullname) {
        $names = explode(' ', $fullname);
        $otherName = implode(' ', array_slice($names, 0, count($names) - 1));
        $familyName = $names[count($names) - 1];
        if (mb_substr($familyName, -1) == 'á') {
            $gender = 'F';
        } else {
            $gender = 'M';
        }
        return array(
            'other_name' => $otherName,
            'family_name' => $familyName,
            'gender' => $gender,
        );
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

    public function getResourceId() {
        return 'person';
    }

}

