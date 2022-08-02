<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read string preferred_lang # TODO enum
 * @property-read \DateTimeInterface born
 * @property-read string id_number
 * @property-read string born_id
 * @property-read string phone
 * @property-read string im
 * @property-read string note
 * @property-read string uk_login
 * @property-read string account
 * @property-read \DateTimeInterface agreed
 * @property-read string birthplace
 * @property-read string citizenship
 * @property-read int health_insurance
 * @property-read string employer
 * @property-read string academic_degree_prefix
 * @property-read string academic_degree_suffix
 * @property-read string email
 * @property-read string origin
 * @property-read string career
 * @property-read string homepage
 * @property-read string fb_id
 * @property-read string linkedin_id
 * @property-read string phone_parent_d
 * @property-read string phone_parent_m
 * @property-read string duplicates
 * @property-read string email_parent_d
 * @property-read string email_parent_m
 * @property-read string pizza
 */
class PersonInfoModel extends Model
{
}
