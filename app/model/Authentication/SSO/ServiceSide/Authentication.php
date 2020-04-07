<?php

namespace FKSDB\Authentication\SSO\ServiceSide;

use FKSDB\Authentication\SSO\IGlobalSession;

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

    /**
     * Authentication constructor.
     * @param IGlobalSession $globalSession
     * @param $loginURL
     * @param $logoutURL
     */
    function __construct(IGlobalSession $globalSession, $loginURL, $logoutURL) {
        $this->globalSession = $globalSession;
        $this->loginURL = $loginURL;
        $this->logoutURL = $logoutURL;
    }

    /**
     * @return bool
     */
    public function isAuthenticated() {
        return isset($this->globalSession[IGlobalSession::UID]);
    }

    /**
     * @return mixed
     */
    public function getIdentity() {
        return $this->globalSession[IGlobalSession::UID];
    }

    /**
     * @param null $backlink
     */
    public function login($backlink = null) {
        $backlink = $backlink ? : $this->getDefaultBacklink();

        $data = [
            self::PARAM_BACKLINK => $backlink,
            self::PARAM_FLAG => self::FLAG_SSO_LOGIN,
        ];

        $redirectURL = $this->setHttpParams($this->loginURL, $data);

        header("Location: $redirectURL", true);
        echo "<h1>Redirect</h1>\n\n<p><a href=\"" . htmlspecialchars($redirectURL) . "\">Please click here to continue</a>.</p>";
        exit;
    }

    /**
     * @param null $backlink
     */
    public function logout($backlink = null) {
        $backlink = $backlink ? : $this->getDefaultBacklink();

        $data = [
            self::PARAM_BACKLINK => $backlink,
            self::PARAM_FLAG => self::FLAG_SSO_LOGIN,
            self::PARAM_GSID => $this->globalSession->getId(),
        ];

        $redirectURL = $this->setHttpParams($this->logoutURL, $data);

        header("Location: $redirectURL", true);
        echo "<h1>Redirect</h1>\n\n<p><a href=\"" . htmlspecialchars($redirectURL) . "\">Please click here to continue</a>.</p>";
        exit;
    }

    /**
     * @return mixed
     */
    private function getDefaultBacklink() {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @param $url
     * @param $params
     * @return string
     */
    private function setHttpParams($url, $params) {
        $query = http_build_query($params, false, '&');

        if (preg_match('/\?/', $url)) { // very simplistic test where URL contains query part
            $url = $url . '&' . $query;
        } else {
            $url = $url . '?' . $query;
        }

        return $url;
    }

}
