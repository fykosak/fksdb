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
abstract class AbstractAuthenticator /* implements IAuthenticator */ {

    /** @var \FKSDB\ORM\Services\ServiceLogin */
    protected $serviceLogin;

    /**
     * @var \FKSDB\YearCalculator
     */
    protected $yearCalculator;

    /**
     * AbstractAuthenticator constructor.
     * @param ServiceLogin $serviceLogin
     * @param YearCalculator $yearCalculator
     */
    function __construct(ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        $this->serviceLogin = $serviceLogin;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelLogin $login
     */
    protected function logAuthentication(ModelLogin $login) {
        $login->last_login = DateTime::from(time());
        $this->serviceLogin->save($login);
    }

}
