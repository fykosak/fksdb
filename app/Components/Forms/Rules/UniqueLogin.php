<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use Nette\Forms\Controls\BaseControl;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UniqueLogin {

    private ServiceLogin $serviceLogin;

    private ?ModelLogin$ignoredLogin = null;

    /**
     * UniqueLogin constructor.
     * @param ServiceLogin $serviceLogin
     */
    public function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function getIgnoredLogin(): ?ModelLogin {
        return $this->ignoredLogin;
    }

    public function setIgnoredLogin(?ModelLogin $ignoredLogin): void {
        $this->ignoredLogin = $ignoredLogin;
    }

    public function __invoke(BaseControl $control): bool {
        $login = $control->getValue();

        if (!$login) {
            return true;
        }
        $conflicts = $this->serviceLogin->getTable()->where(['login' => $login]);
        if ($this->ignoredLogin && $this->ignoredLogin->login_id) {
            $conflicts->where('NOT login_id = ?', $this->ignoredLogin->login_id);
        }
        return $conflicts->count() === 0;
    }
}
