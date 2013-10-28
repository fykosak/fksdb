<?php

use Authentication\FacebookAuthenticator;
use Authentication\TokenAuthenticator;
use FKS\Authentication\SSO\IGlobalSession;
use Nette\Application\UI\Form;
use Nette\DateTime;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;

final class AuthenticationPresenter extends BasePresenter {

    const PARAM_GSID = 'gsid';
    /** @const Indicates that page is accessed via dispatch from the login page. */
    const PARAM_DISPATCH = 'dispatch';
    /** @const Reason why the user has been logged out. */
    const PARAM_REASON = 'reason';
    const FLAG_SSO = 'sso';
    const REASON_TIMEOUT = '1';
    const REASON_AUTH = '2';

    /** @persistent */
    public $backlink = '';

    /** @persistent */
    public $flag;

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @var FacebookAuthenticator
     */
    private $facebookAuthenticator;

    /**
     * @var ServiceAuthToken
     */
    private $serviceAuthToken;

    /**
     * @var IGlobalSession
     */
    private $globalSession;

    public function injectFacebook(Facebook $facebook) {
        $this->facebook = $facebook;
    }

    public function injectFacebookAuthenticator(FacebookAuthenticator $facebookAuthenticator) {
        $this->facebookAuthenticator = $facebookAuthenticator;
    }

    public function injectServiceAuthToken(ServiceAuthToken $serviceAuthToken) {
        $this->serviceAuthToken = $serviceAuthToken;
    }

    public function injectGlobalSession(IGlobalSession $globalSession) {
        $this->globalSession = $globalSession;
    }

    public function actionLogout() {
        $subdomainAuth = $this->context->parameters['subdomain']['auth'];
        $subdomain = $this->getParam('subdomain');

        if ($subdomain != $subdomainAuth) {
            // local logout
            $this->getUser()->logout(true);

            // redirect to global logout
            $params = array(
                'subdomain' => $subdomainAuth,
                self::PARAM_GSID => $this->globalSession->getId(),
            );
            $url = $this->link('//this', $params);
            $this->redirectUrl($url);
            return;
        }
        // else: $subdomain == $subdomainAuth
        // -> check for the GSID parameter

        if ($this->isLoggedIn()) {
            $this->getUser()->logout(true); //clear identity            
        } else if ($this->getParam(self::PARAM_GSID)) { // global session may exist but central login doesn't know it (e.g. expired its session)
            // We restart the global session with provided parameter.
            // This is secure as only harm an attacker can make to the user is to log him out.
            $this->globalSession->destroy();

            // If the GSID is valid, we'll obtain user's identity and log him out promptly.
            $this->globalSession->start($this->getParam(self::PARAM_GSID));
            $this->getUser()->logout(true);
        }
        $this->flashMessage("Byl jste odhlášen.", self::FLASH_SUCCESS);
        $this->backlinkRedirect();
        $this->redirect("login");
    }

    public function actionLogin() {
        if ($this->isLoggedIn()) {
            $login = $this->getUser()->getIdentity();
            $this->backlinkRedirect($login);
            $this->initialRedirect($login);
        } else if ($this->getParam(self::PARAM_REASON)) {
            switch ($this->getParam(self::PARAM_REASON)) {
                case self::REASON_TIMEOUT:
                    $this->flashMessage(_('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.'), self::FLASH_INFO);
                    break;
                case self::REASON_AUTH:
                    $this->flashMessage(_('Stránka požaduje přihlášení.'), self::FLASH_ERROR);
                    break;
            }
        }
    }

    public function actionFbLogin() {
        try {
            $me = $this->facebook->api('/me');
            $identity = $this->facebookAuthenticator->authenticate($me);

            $this->getUser()->login($identity);
            $login = $this->getUser()->getIdentity();
            $this->initialRedirect($login);
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        } catch (FacebookApiException $e) {
            $fbUrl = $this->getFbLoginUrl();
            $this->redirectUri($fbUrl);
        }
    }

    public function titleLogin() {
        $this->setTitle(_('Login'));
    }

    public function renderLogin() {
        $this->template->fbUrl = $this->getFbLoginUrl();
    }

    private function getFbLoginUrl() {
        $fbUrl = $this->facebook->getLoginUrl(array(
            'scope' => 'email',
            'redirect_uri' => $this->link('//fbLogin'), // absolute
        ));
        return $fbUrl;
    }

    /**
     * This workaround is here because LoginUser storage
     * returns false when only global login exists.
     * False is return in order to AuthenticatedPresenter to correctly login the user.
     * 
     * @return bool
     */
    private function isLoggedIn() {
        return $this->getUser()->isLoggedIn() || isset($this->globalSession[IGlobalSession::UID]);
    }

    /*     * ******************* components ****************************** */

    /**
     * Login form component factory.
     * @return mixed
     */
    protected function createComponentLoginForm() {
        $form = new Form($this, 'loginForm');
        $form->addText('id', 'Přihlašovací jméno nebo email')
                ->addRule(Form::FILLED, 'Zadejte přihlašovací jméno nebo emailovou adresu.');

        $form->addPassword('password', 'Heslo')
                ->addRule(Form::FILLED, 'Zadejte heslo.');
        //$form->addCheckbox('remember', 'Zapamatovat si přihlášení');

        $form->addSubmit('login', 'Přihlásit');

        $form->addProtection('Odešlete prosím formulář znovu. Vypršela jeho časová platnost nebo máte vypnuté cookies (tedy zapnout).');

        $form->onSuccess[] = callback($this, 'loginFormSubmitted');
        return $form;
    }

    public function loginFormSubmitted($form) {
        try {
//            if ($form['remember']->value) {
//                $this->user->setExpiration('+20 days', false);
//            } else {
//                $this->user->setExpiration(0, true);
//            }
            $this->user->login($form['id']->value, $form['password']->value);
            $login = $this->user->getIdentity();

            $this->backlinkRedirect($login);
            $this->initialRedirect($login);
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    private function backlinkRedirect($login = null) {
        if (!$this->backlink) {
            return;
        }
        $this->restoreRequest($this->backlink);

        $url = new Url($this->backlink);
        $this->backlink = null;

        if ($this->flag == self::FLAG_SSO && $login) {
            $gsid = $this->globalSession->getId();
            $expiration = $this->context->parameters['authentication']['sso']['tokenExpiration'];
            $until = DateTime::from($expiration);
            $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_SSO, $until, $gsid);
            $url->appendQuery(array(TokenAuthenticator::PARAM_AUTH_TOKEN => $token->token));
        }

        if ($url->getHost()) { // this would indicate absolute URL
            if (in_array($url->getHost(), $this->context->parameters['authentication']['backlinkHosts'])) {
                $this->redirectUrl((string) $url, 303);
            } else {
                $this->flashMessage(sprintf(_('Nedovolený backlink %s.'), (string) $url), self::FLASH_ERROR);
            }
        }
    }

    private function initialRedirect($login) {
        if (count($login->getActiveOrgs($this->yearCalculator)) > 0) {
            $this->redirect(':Org:Dashboard:', array(self::PARAM_DISPATCH => 1));
        } else {
            $this->redirect(':Public:Dashboard:', array(self::PARAM_DISPATCH => 1));
        }
        // or else redirect to page suggesting registration
    }

}