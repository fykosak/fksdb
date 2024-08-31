<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Nette\Utils\DateTime;

/**
 * @property-read int $token_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read string $token
 * @property-read string $type
 * @property-read string|null $data
 * @property-read DateTime $since
 * @property-read DateTime $until
 */
final class AuthTokenModel extends Model
{
    public function isActive(): bool
    {
        return $this->since <= new \DateTime() && $this->until >= new \DateTime();
    }
}
