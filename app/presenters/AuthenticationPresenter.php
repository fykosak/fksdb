<?php

final class AuthenticationPresenter extends BasePresenter {

    /** @persistent */
    public $backlink = '';

    public function actionLogout() {
        if ($this->getUser()->isLoggedIn()) {
            $a = $this->getUser()->getIdentity()->gender == 'F' ? "a" : "";
            $this->getUser()->logout(true); //clear identity

            $this->flashMessage("Byl$a jste odhlášen$a.");
        }
        $this->redirect("login");
    }

    public function actionLogin() {
        //TODO: udělat i restoreRequest, pokud je (je to bezpečné?)
//        if ($this->getUser()->isLoggedIn()) {
//            $this->redirect("Dashboard:default");
//        }
    }

    /*     * ******************* components ****************************** */

    /**
     * Login form component factory.
     * @return mixed
     */
    protected function createComponentLoginForm() {
        $form = new NAppForm($this, 'loginForm');
        $form->addText('id', 'Přihlašovací jméno/email')
                ->addRule(NForm::FILLED, 'Zadejte přihlašovací jméno nebo emailovou adresu.');

        $form->addPassword('password', 'Heslo')
                ->addRule(NForm::FILLED, 'Zadejte heslo.');
        //$form->addCheckbox('remember', 'Zapamatovat si přihlášení');

        $form->addSubmit('login', 'Přihlásit');

        $form->addProtection('Odešlete prosím formulář znovu. Vypršela jeho časová platnost nebo máte vypnuté cookies (tedy zapnput).');

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
            $this->application->restoreRequest($this->backlink);
            $this->redirect('Dashboard:default');
        } catch (NAuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

}