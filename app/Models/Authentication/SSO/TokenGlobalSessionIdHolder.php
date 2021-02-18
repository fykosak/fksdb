<?php

namespace FKSDB\Models\Authentication\SSO;

use FKSDB\Models\Authentication\TokenAuthenticator;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Services\ServiceAuthToken;
use Nette\Http\Request;
use Nette\Http\Session;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TokenGlobalSessionIdHolder implements GlobalSessionIdHolder {

    public const SESSION_NS = 'sso';
    public const GSID_KEY = 'gsid';
    private Session $session;
    private ServiceAuthToken $serviceAuthToken;
    private Request $request;

    public function __construct(Session $session, ServiceAuthToken $serviceAuthToken, Request $request) {
        $this->session = $session;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->request = $request;
    }

    public function getGlobalSessionId(): ?string {
        // try obtain GSID from auth token in URL
        $tokenData = $this->request->getQuery(TokenAuthenticator::PARAM_AUTH_TOKEN);
        $token = $tokenData ? $this->serviceAuthToken->verifyToken($tokenData) : null;
        if ($token && $token->type == ModelAuthToken::TYPE_SSO) {
            $gsid = $token->data;
            $this->setGlobalSessionId($gsid); // so later we know our GSID

            return $gsid;
        }

        // fallback on session
        $section = $this->session->getSection(self::SESSION_NS);
        if (isset($section[self::GSID_KEY])) {
            return $section[self::GSID_KEY];
        } else {
            return null;
        }
    }

    public function setGlobalSessionId(?string $globalSessionId): void {
        $section = $this->session->getSection(self::SESSION_NS);
        $section[self::GSID_KEY] = $globalSessionId;
    }
}
