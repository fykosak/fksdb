<?php

namespace FKSDB\Model\Authentication;

use FKSDB\Model\ORM\Models\ModelLogin;
use FKSDB\Model\ORM\Services\ServiceLogin;
use FKSDB\Model\YearCalculator;
use Nette\Utils\DateTime;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @note IAuthenticator interface is not explicitly implemented due to 'array'
 * type hint at authenticate method.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractAuthenticator /* implements IAuthenticator */
{
    protected ServiceLogin $serviceLogin;
    protected YearCalculator $yearCalculator;

    public function __construct(ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        $this->serviceLogin = $serviceLogin;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param ModelLogin $login
     * @throws \Exception
     */
    protected function logAuthentication(ModelLogin $login): void {
        $this->serviceLogin->updateModel2($login, ['last_login' => DateTime::from(time())]);
    }
}
