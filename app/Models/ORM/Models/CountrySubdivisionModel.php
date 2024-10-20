<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $country_subdivision_id
 * @property-read string $code
 * @property-read string $name
 * @property-read int $country_id
 * @property-read CountryModel $country
 */
final class CountrySubdivisionModel extends Model
{
}
