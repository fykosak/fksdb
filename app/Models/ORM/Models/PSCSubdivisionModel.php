<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read string $psc
 * @property-read int $country_subdivision_id
 * @property-read CountrySubdivisionModel $country_subdivision
 */
final class PSCSubdivisionModel extends Model
{
}
