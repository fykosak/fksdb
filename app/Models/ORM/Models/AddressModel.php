<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int address_id
 * @property-read string first_row
 * @property-read string second_row
 * @property-read string target
 * @property-read string city
 * @property-read string postal_code
 * @property-read int region_id
 * @property-read RegionModel|null region
 */
class AddressModel extends Model
{
}
