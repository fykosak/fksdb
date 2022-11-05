<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read string token
 * @property-read LoginModel login
 * @property-read int login_id
 * @property-read string data
 * @property-read AuthTokenType type
 * @property-read \DateTimeInterface until
 */
class AuthTokenModel extends Model
{
}
