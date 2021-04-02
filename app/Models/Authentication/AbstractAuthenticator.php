<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use Nette\Utils\DateTime;

/**
 * @note IAuthenticator interface is not explicitly implemented due to 'array'
 * type hint at authenticate method.
 */
abstract class AbstractAuthenticator /* implements IAuthenticator */
{
    protected ServiceLogin $serviceLogin;

    public function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    /**
     * @param ModelLogin $login
     * @throws \Exception
     */
    protected function logAuthentication(ModelLogin $login): void {
        $this->serviceLogin->updateModel($login, ['last_login' => DateTime::from(time())]);
    }
}
