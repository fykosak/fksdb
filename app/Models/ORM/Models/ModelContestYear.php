<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int contest_id
 * @property-read int ac_year
 * @property-read int year
 * @property-read ModelContest contest
 */
class ModelContestYear extends Model
{
}
