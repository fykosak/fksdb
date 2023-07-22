<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read string $token
 * @property-read LoginModel $login
 * @property-read int $login_id
 * @property-read string $data
 * @property-read string $type
 * @property-read \DateTimeInterface $until
 * TODO
 */
final class AuthTokenModel extends Model
{
}
