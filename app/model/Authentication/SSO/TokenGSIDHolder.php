<?php

namespace Authentication\SSO;

use Authentication\TokenAuthenticator;
use FKSDB\Authentication\SSO\IGSIDHolder;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Services\ServiceAuthToken;
use Nette\Http\Request;
use Nette\Http\Session;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TokenGSIDHolder implements IGSIDHolder {

    const SESSION_NS = 'sso';
    const GSID_KEY = 'gsid';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \FKSDB\ORM\Services\ServiceAuthToken
     */
    private $serviceAuthToken;

    /**
     *
     * @var Request
     */
    private $request;

    /**
     * TokenGSIDHolder constructor.
     * @param Session $session
     * @param ServiceAuthToken $serviceAuthToken
     * @param Request $request
     */
    public function __construct(Session $session, ServiceAuthToken $serviceAuthToken, Request $request) {
        $this->session = $session;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->request = $request;
    }

    /**
     * @return mixed|null|string
     */
    public function getGSID() {
        // try obtain GSID from auth token in URL
        $tokenData = $this->request->getQuery(TokenAuthenticator::PARAM_AUTH_TOKEN);
        $token = $tokenData ? $this->serviceAuthToken->verifyToken($tokenData) : null;
        if ($token && $token->type == ModelAuthToken::TYPE_SSO) {
            $gsid = $token->data;
            $this->setGSID($gsid); // so later we know our GSID

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

    /**
     * @param $gsid
     */
    public function setGSID($gsid) {
        $section = $this->session->getSection(self::SESSION_NS);
        $section[self::GSID_KEY] = $gsid;
    }

}
