<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Services\Exceptions\RejectedEmailException;
use Fykosak\NetteORM\Model\DummyModel;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<DummyModel>
 */
final class UnsubscribedEmailService extends Service
{
    /**
     * @throws RejectedEmailException
     */
    public function checkEmail(string $email): void
    {
        $row = $this->getTable()->where('email_hash = SHA1(?)', $email)->fetch();
        if ($row) {
            throw new RejectedEmailException();
        }
    }
}
