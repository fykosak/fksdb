<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Rules;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;

class UniqueLogin
{
    private LoginService $loginService;
    private ?LoginModel $ignoredLogin = null;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(LoginService $loginService): void
    {
        $this->loginService = $loginService;
    }

    public function setIgnoredLogin(LoginModel $ignoredLogin): void
    {
        $this->ignoredLogin = $ignoredLogin;
    }

    public function __invoke(BaseControl $control): bool
    {
        $login = $control->getValue();
        if (!$login) {
            return true;
        }
        $conflicts = $this->loginService->getTable()->where(['login' => $login]);
        if (isset($this->ignoredLogin)) {
            $conflicts->where('NOT login_id = ?', $this->ignoredLogin->login_id);
        }
        if (count($conflicts) > 0) {
            return false;
        }
        return true;
    }
}
