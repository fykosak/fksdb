<?php

use Authentication\FacebookAuthenticator;
use Authentication\TokenAuthenticator;
use FKS\Authentication\SSO\IGlobalSession;
use Nette\Application\UI\Form;
use Nette\DateTime;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;

final class AuthenticationPresenter extends BasePresenter {

    const FLAG_SSO = 'sso';

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
        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout(true); //clear identity

            $this->flashMessage("Byl jste odhlášen.");
        }
        $this->backlinkRedirect();
        $this->redirect("login");
    }

    public function actionLogin() {
        if ($this->getUser()->isLoggedIn()) {
            $login = $this->getUser()->getIdentity();
            $this->backlinkRedirect($login);
            $this->initialRedirect($login);
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
            $this->flashMessage($e->getMessage(), 'error');
        } catch (FacebookApiException $e) {
            $fbUrl = $this->getFbLoginUrl();
            $this->redirectUri($fbUrl);
        }
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

    /*     * ******************* components ****************************** */

    /**
     * Login form component factory.
     * @return mixed
     */
    protected function createComponentLoginForm() {
        $form = new Form($this, 'loginForm');
        $form->addText('id', 'Přihlašovací jméno/email')
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
            $expiration = $this->context->parameters['globalSession']['tokenExpiration'];
            $until = DateTime::from($expiration);
            $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_SSO, $until, $gsid);
            $url->appendQuery(array(TokenAuthenticator::PARAM_AUTH_TOKEN => $token->token));
        }

        if ($url->getHost()) { // this would indicate absolute URL
            if (in_array($url->getHost(), $this->context->parameters['authentication']['backlinkHosts'])) {
                $this->redirectUrl((string) $url, 303);
            } else {
                $this->flashMessage(sprintf(_('Nedovolený backlink %s.'), (string) $url), 'error');
            }
        }
    }

    private function initialRedirect($login) {
        if (count($login->getActiveOrgs($this->yearCalculator)) > 0) {
            $this->redirect(':Org:Dashboard:');
        } else {
            $this->redirect(':Public:Dashboard:');
        }
    }

}