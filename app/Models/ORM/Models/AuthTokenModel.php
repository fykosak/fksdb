<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int $token_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read string $token
 * @property-read string $type
 * @property-read string|null $data
 * @property-read \DateTimeInterface $since
 * @property-read \DateTimeInterface|null $until
 */
final class AuthTokenModel extends Model
{
}
