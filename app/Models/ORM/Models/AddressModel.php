<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $address_id
 * @property-read string|null $first_row
 * @property-read string|null $second_row
 * @property-read string $target
 * @property-read string $city
 * @property-read string|null $postal_code
 * @property-read int $country_id
 * @property-read CountryModel $country
 * @property-read int|null $country_subdivision_id
 * @property-read CountrySubdivisionModel|null $country_subdivision
 */
final class AddressModel extends Model
{
}
