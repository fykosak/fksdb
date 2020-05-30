<?php

namespace Authentication;

use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\YearCalculator;
use Nette\Utils\DateTime;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @note IAuthenticator interface is not explixitly implemented due to 'array'
 * type hint at authenticate method.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractAuthenticator /* implements IAuthenticator */
{

    /** @var ServiceLogin */
    protected $serviceLogin;

    /**
     * @var YearCalculator
     */
    protected $yearCalculator;

    /**
     * AbstractAuthenticator constructor.
     * @param ServiceLogin $serviceLogin
     * @param YearCalculator $yearCalculator
     */
    public function __construct(ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        $this->serviceLogin = $serviceLogin;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param ModelLogin $login
     * @return void
     */
    protected function logAuthentication(ModelLogin $login) {
        $this->serviceLogin->updateModel2($login, ['last_login' => DateTime::from(time())]);
    }

}
