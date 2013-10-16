<?php

use Authentication\FacebookAuthenticator;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class AuthenticationPresenter extends BasePresenter {

    /** @persistent */
    public $backlink = '';

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @var FacebookAuthenticator
     */
    private $facebookAuthenticator;

    public function injectFacebook(Facebook $facebook) {
        $this->facebook = $facebook;
    }

    public function injectFacebookAuthenticator(FacebookAuthenticator $facebookAuthenticator) {
        $this->facebookAuthenticator = $facebookAuthenticator;
    }

    public function actionLogout() {
        if ($this->getUser()->isLoggedIn()) {
            $a = $this->getUser()->getIdentity()->getPerson()->gender == 'F' ? "a" : "";
            $this->getUser()->logout(true); //clear identity

            $this->flashMessage("Byl$a jste odhlášen$a.");
        }
        $this->redirect("login");
    }

    public function actionLogin() {
        if ($this->getUser()->isLoggedIn()) {
            $person = $this->getUser()->getIdentity()->getPerson();
            $this->initialRedirect($person);
        }
    }

    public function actionFbLogin() {
        try {
            $me = $this->facebook->api('/me');
            $identity = $this->facebookAuthenticator->authenticate($me);

            $this->getUser()->login($identity);
            $person = $this->getUser()->getIdentity()->getPerson();
            $this->initialRedirect($person);
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
            $person = $this->user->getIdentity()->getPerson();

            $this->restoreRequest($this->backlink);
            $this->initialRedirect($person);
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    private function initialRedirect($person) {
        if (!$person) {
            throw new AuthenticationException('Impersonal logins not supported.'); //TODO implement logic for impersonal logins
        } else if (count($person->getActiveOrgs($this->yearCalculator)) > 0) {
            $this->redirect(':Org:Dashboard:default');
        } else {
            $this->redirect(':Public:Dashboard:default');
        }
    }

}