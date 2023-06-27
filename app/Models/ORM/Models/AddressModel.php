<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int $address_id
 * @property-read string $first_row
 * @property-read string $second_row
 * @property-read string $target
 * @property-read string $city
 * @property-read string $postal_code
 * @property-read int|null $country_id
 * @property-read CountryModel $country
 * @property-read int|null $country_subdivision_id
 * @property-read CountrySubdivisionModel $country_subdivision
 */
class AddressModel extends Model
{
}
