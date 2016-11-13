<?php

namespace Authentication;

use FKS\Config\GlobalParameters;
use FullHttpRequest;
use Github\Events\Event;
use ModelLogin;
use Nette\Http\Request;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;
use ServiceLogin;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GithubAuthenticator extends AbstractAuthenticator {

    const PARAM_AUTH_TOKEN = 'at';
    const SESSION_NS = 'auth';
	const HTTP_AUTH_HEADER = 'X-Hub-Signature';

    /**
     * @var GlobalParameters
     */
    private $globalParameters;

    function __construct(GlobalParameters $globalParameters, ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->globalParameters = $globalParameters;
    }

    /**
     * @param Request $request
     * @return ModelLogin
     * @throws AuthenticationException
     */
    public function authenticate(FullHttpRequest $request) {
        $loginName = $this->globalParameters['github']['login'];
        $secret = $this->globalParameters['github']['secret'];

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
