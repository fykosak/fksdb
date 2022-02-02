<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Rules;

use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use Nette\Forms\Controls\BaseControl;

class UniqueLogin
{
    private ServiceLogin $serviceLogin;
    private ?ModelLogin $ignoredLogin;

    public function __construct(ServiceLogin $serviceLogin)
    {
        $this->serviceLogin = $serviceLogin;
    }

    public function setIgnoredLogin(?ModelLogin $ignoredLogin): void
    {
        $this->ignoredLogin = $ignoredLogin;
    }

    public function __invoke(BaseControl $control): bool
    {
        $login = $control->getValue();
        if (!$login) {
            return true;
        }
        $conflicts = $this->serviceLogin->getTable()->where(['login' => $login]);
        if ($this->ignoredLogin && $this->ignoredLogin->login_id) {
            $conflicts->where('NOT login_id = ?', $this->ignoredLogin->login_id);
        }
        if (count($conflicts) > 0) {
            return false;
        }
        return true;
    }
}
