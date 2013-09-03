<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPerson extends AbstractModelSingle {

    /**
     * @return AbstractModelSingle|null
     */
    public function getLogin() {
        return $this->ref(DbNames::TAB_LOGIN, 'person_id');
    }

    /**
     * @return AbstractModelSingle|null
     */
    public function getInfo() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->ref(DbNames::TAB_PERSON_INFO, 'person_id');
    }

    public function getContestants() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_CONTESTANT, 'person_id');
    }

    public function getSpamees() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_SPAMEE, 'person_id');
    }

    public function getPostContacts() {
        if (!isset($this->person_id)) {
            $this->person_id = null;
        }
        return $this->related(DbNames::TAB_POST_CONTACT, 'person_id');
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
            $year = $yearCalculator->getCurrentYear($org->contest_id);
            if ($org->since <= $year && ($org->until === null || $org->until >= $year)) {
                $result[$org->org_id] = ModelOrg::createFromTableRow($org);
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
            $year = $yearCalculator->getCurrentYear($contestant->contest_id);
            if ($contestant->year == $year) {
                $result[$contestant->ct_id] = ModelContestant::createFromTableRow($contestant);
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

}

