<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\NoLoginException;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\Github\Events\Event;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;

class GithubAuthenticator extends AbstractAuthenticator {

    public const PARAM_AUTH_TOKEN = 'at';
    public const SESSION_NS = 'auth';
    public const HTTP_AUTH_HEADER = 'X-Hub-Signature';

    private Container $container;

    public function __construct(ServiceLogin $serviceLogin, Container $container) {
        parent::__construct($serviceLogin);
        $this->container = $container;
    }

    /**
     * @param IRequest $request
     * @return ModelLogin
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws NoLoginException
     * @throws \Exception
     */
    public function authenticate(IRequest $request): ModelLogin {
        $loginName = $this->container->getParameters()['github']['login'];
        $secret = $this->container->getParameters()['github']['secret'];

        if (!$request->getHeader(Event::HTTP_HEADER)) {
            throw new InvalidArgumentException(_('Expected header X-Github-Event'));
        }

        $signature = $request->getHeader(self::HTTP_AUTH_HEADER);
        if (!$signature) {
            throw new AuthenticationException(_('Expected header X-Hub-Signature.'));
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
