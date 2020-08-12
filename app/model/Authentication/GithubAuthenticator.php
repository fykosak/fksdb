<?php

namespace FKSDB\Authentication;

use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\YearCalculator;
use FKSDB\FullHttpRequest;
use FKSDB\Github\Events\Event;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GithubAuthenticator extends AbstractAuthenticator {

    const PARAM_AUTH_TOKEN = 'at';
    const SESSION_NS = 'auth';
    const HTTP_AUTH_HEADER = 'X-Hub-Signature';

    private Container $container;

    /**
     * GithubAuthenticator constructor.
     * @param ServiceLogin $serviceLogin
     * @param YearCalculator $yearCalculator
     * @param Container $container
     */
    public function __construct(ServiceLogin $serviceLogin, YearCalculator $yearCalculator, Container $container) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->container = $container;
    }

    /**
     * @param FullHttpRequest $request
     * @return ModelLogin
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws NoLoginException
     */
    public function authenticate(FullHttpRequest $request) {
        $loginName = $this->container->getParameters()['github']['login'];
        $secret = $this->container->getParameters()['github']['secret'];

        if (!$request->getRequest()->getHeader(Event::HTTP_HEADER)) {
            throw new InvalidArgumentException(_('Očekávána hlavička X-Github-Event'));
        }

        $signature = $request->getRequest()->getHeader(self::HTTP_AUTH_HEADER);
        if (!$signature) {
            throw new AuthenticationException(_('Očekávána hlavička X-Hub-Signature.'));
        }

        $expectedHash = 'sha1=' . hash_hmac('sha1', $request->getPayload(), $secret, false);

        if ($signature !== $expectedHash) {
            //throw new AuthenticationException(_('Nesprávný hash požadavku.'));
        }
        /** @var ModelLogin $login */
        $login = $this->serviceLogin->getTable()->where('login = ?', $loginName)->fetch();
        if (!$login) {
            throw new NoLoginException();
        }
        if (!$login->active) {
            throw new InactiveLoginException();
        }

        $this->logAuthentication($login);

        return $login;
    }
}
