<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<LoginModel>
 */
final class LoginService extends Service
{
    public function createLogin(PersonModel $person, ?string $login = null, ?string $password = null): LoginModel
    {
        /** @var LoginModel $login */
        $login = $this->storeModel([
            'person_id' => $person->person_id,
            'login' => $login,
            'active' => 1,
        ]);

        /* Must be done after login_id is allocated. */
        if ($password) {
            $hash = $login->calculateHash($password);
            $this->storeModel(['hash' => $hash], $login);
        }
        return $login;
    }
}
