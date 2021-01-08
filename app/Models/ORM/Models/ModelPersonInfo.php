<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read string email
 * @property-read string phone
 * @property-read string phone_parent_m
 * @property-read string phone_parent_d
 * @property-read string email_parent_m
 * @property-read string email_parent_d
 * @property-read string born_id
 * @property-read int health_insurance
 * @property-read \DateTimeInterface agreed
 * @property-read \DateTimeInterface born
 * @property-read int person_id
 * @property-read string id_number
 * @property-read string im
 * @property-read string note
 * @property-read string uk_login
 * @property-read string account
 * @property-read string birthplace
 * @property-read string origin
 * @property-read string career
 * @property-read string homepage
 * @property-read string fb_id
 * @property-read string linkedin_id
 * @property-read string duplicates
 * @property-read string citizenship
 * @property-read string employer
 * @property-read string academic_degree_prefix
 * @property-read string academic_degree_suffix
 * @property-read string preferred_lang
 */
class ModelPersonInfo extends OldAbstractModelSingle {

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }
}
