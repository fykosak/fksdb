<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use Nette\Utils\DateTime;
use Tracy\Debugger;

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
     * @throws \Exception
     */
    protected function logAuthentication(ModelLogin $login): void {
        Debugger::log(sprintf('LoginId %s (%s) successfully logged in', $login->login_id, $login->getPerson()), 'auth-log');
        $this->serviceLogin->updateModel($login, ['last_login' => DateTime::from(time())]);
    }
}
