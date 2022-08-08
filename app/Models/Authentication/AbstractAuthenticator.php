<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * @note IAuthenticator interface is not explicitly implemented due to 'array'
 * type hint at authenticate method.
 */
abstract class AbstractAuthenticator /* implements IAuthenticator */
{

    protected LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * @throws \Exception
     */
    protected function logAuthentication(LoginModel $login): void
    {
        Debugger::log(
            sprintf('LoginId %s (%s) successfully logged in', $login->login_id, $login->person),
            'auth-log'
        );
        $this->loginService->updateModel($login, ['last_login' => DateTime::from(time())]);
    }
}
