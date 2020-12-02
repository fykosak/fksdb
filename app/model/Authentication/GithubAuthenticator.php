<?php

namespace FKSDB\Authentication;

use FKSDB\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Authentication\Exceptions\NoLoginException;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\YearCalculator;
use FKSDB\Github\Events\Event;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GithubAuthenticator extends AbstractAuthenticator {

    public const PARAM_AUTH_TOKEN = 'at';
    public const SESSION_NS = 'auth';
    public const HTTP_AUTH_HEADER = 'X-Hub-Signature';

    private Container $container;

    public function __construct(ServiceLogin $serviceLogin, YearCalculator $yearCalculator, Container $container) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->container = $container;
    }

    /**
     * @param IRequest $request
     * @return ModelLogin
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws NoLoginException
     */
    public function authenticate(IRequest $request): ModelLogin {
        $loginName = $this->container->getParameters()['github']['login'];
        $secret = $this->container->getParameters()['github']['secret'];

        if (!$request->getHeader(Event::HTTP_HEADER)) {
            throw new InvalidArgumentException(_('Očekávána hlavička X-Github-Event'));
        }

        $signature = $request->getHeader(self::HTTP_AUTH_HEADER);
        if (!$signature) {
            throw new AuthenticationException(_('Očekávána hlavička X-Hub-Signature.'));
        }

        $expectedHash = 'sha1=' . hash_hmac('sha1', $request->getRawBody(), $secret, false);

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
