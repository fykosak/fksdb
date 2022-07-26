<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Fykosak\NetteORM\Model;

/**
 * @property-read int contest_id
 * @property-read ModelRole role
 * @property-read ModelContest contest
 */
class ModelGrant extends Model
{
}
