<?php

namespace FKS\Authentication\SSO\ServiceSide;

use FKS\Authentication\SSO\IGlobalSession;

/**
 * Wrapper around IGlobalSession that intends to have no outer dependencies.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Authentication {

    const PARAM_BACKLINK = 'backlink';
    const PARAM_FLAG = 'flag';
    const PARAM_GSID = 'gsid';
    const FLAG_SSO_LOGIN = 'sso';

    /**
     * @var IGlobalSession
     */
    private $globalSession;

    /**
     * @var string
     */
    private $loginURL;

    /**
     * @var string
     */
    private $logoutURL;

    function __construct(IGlobalSession $globalSession, $loginURL, $logoutURL) {
        $this->globalSession = $globalSession;
        $this->loginURL = $loginURL;
        $this->logoutURL = $logoutURL;
    }

    public function isAuthenticated() {
        return isset($this->globalSession[IGlobalSession::UID]);
    }

    public function getIdentity() {
        return $this->globalSession[IGlobalSession::UID];
    }

    public function login($backlink = null) {
        $backlink = $backlink ? : $this->getDefaultBacklink();

        $data = array(
            self::PARAM_BACKLINK => $backlink,
            self::PARAM_FLAG => self::SSO_FLAG,
        );

        $redirectURL = $this->setHttpParams($this->loginURL, $data);

        header("Location: $redirectURL", true);
        echo "<h1>Redirect</h1>\n\n<p><a href=\"" . htmlSpecialChars($redirectURL) . "\">Please click here to continue</a>.</p>";
        exit;
    }

    public function logout($backlink = null) {
        $backlink = $backlink ? : $this->getDefaultBacklink();

        $data = array(
            self::PARAM_BACKLINK => $backlink,
            self::PARAM_FLAG => self::SSO_FLAG,
            self::PARAM_GSID => $this->globalSession->getId(),
        );

        $redirectURL = $this->setHttpParams($this->logoutURL, $data);

        header("Location: $redirectURL", true);
        echo "<h1>Redirect</h1>\n\n<p><a href=\"" . htmlSpecialChars($redirectURL) . "\">Please click here to continue</a>.</p>";
        exit;
    }

    private function getDefaultBacklink() {
        return $_SERVER['REQUEST_URI'];
    }

    private function setHttpParams($url, $params) {
        $query = http_build_query($params);

        if (preg_match('/\?/', $url)) { // very simplistic test where URL contains query part
            $url = $url . '&' . $query;
        } else {
            $url = $url . '?' . $query;
        }
        
        return $url;
    }

}
