<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Model;

/**
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
 * @property-read ActiveRow person
 * @property-read string id_number
 * @property-read string im
 * @property-read string note
 * @property-read string uk_login
 * @property-read string isic_number
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
 * @property-read string pizza
 */
class ModelPersonInfo extends Model
{

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }
}
