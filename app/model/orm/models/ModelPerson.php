<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPerson extends AbstractModelSingle implements IIdentity {

    public static function createFromTableRow(NTableRow $row) {
        return new self($row->toArray(), $row->getTable());
    }

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
        return $this->ref(DbNames::TAB_PERSON_INFO, 'person_id');
    }

    public function getContestants() {
        $this->person_id = null;
        return $this->related(DbNames::TAB_CONTESTANT, 'person_id');
    }

    public function getLastContestant(ModelContest $contest) {
        $contestant = $this->getContestants()->where('contest_id = ?', $contest->contest_id)->order('year DESC')->fetch();

        if ($contestant) {
            return ModelContestant::createFromTableRow($contestant);
        } else {
            return null;
        }
    }

    public function getFullname() {
        return $this->first_name . ' ' . $this->last_name;
    }

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
     * @param str $fullname
     * @return array
     */
    public static function parseFullname($fullname) {
        $names = explode(' ', $fullname);
        $firstName = implode(' ', array_slice($names, 0, count($names) - 1));
        $lastName = $names[count($names)-1];
        if (mb_substr($lastName, -1) == 'á') {
            $gender = 'F';
        } else {
            $gender = 'M';
        }
        return array(
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $gender,
        );
    }

    // ----- IIdentity implementation ----------

    public function getId() {
        return $this->person_id;
    }

    public function getRoles() {
        return array();
    }

}

