<?php

use Authentication\AccountManager;
use Authentication\FacebookAuthenticator;
use Authentication\LoginUserStorage;
use Authentication\PasswordAuthenticator;
use Authentication\RecoveryException;
use Authentication\TokenAuthenticator;
use FKS\Authentication\SSO\IGlobalSession;
use FKS\Authentication\SSO\ServiceSide\Authentication;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
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
    /** @const Various modes of authentication. */
    const PARAM_FLAG = 'flag';
    /** @const User is shown the login form if he's not authenticated. */
    const FLAG_SSO_LOGIN = Authentication::FLAG_SSO_LOGIN;
    /** @const Only check of authentication with subsequent backlink redirect. */
    const FLAG_SSO_PROBE = 'ssop';
    const REASON_TIMEOUT = '1';
    const REASON_AUTH = '2';

    /** @persistent */
    public $backlink = '';

    /** @persistent */
    public $flag;

    /**
     * @var Facebook
     */
    //private $facebook;

    /**
     * @var FacebookAuthenticator
     */
    //private $facebookAuthenticator;

    /**
     * @var ServiceAuthToken
     */
    private $serviceAuthToken;

    /**
     * @var IGlobalSession
     */
    private $globalSession;

    /**
     * @var PasswordAuthenticator
     */
    private $passwordAuthenticator;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    protected function createComponentLanguageChooser() {
        $control = new \FKSDB\Components\Controls\ContestNav\LanguageChooser($this->session);
        return $control;
    }

    /*
        public function injectFacebook(Facebook $facebook) {
            $this->facebook = $facebook;
        }

        public function injectFacebookAuthenticator(FacebookAuthenticator $facebookAuthenticator) {
            $this->facebookAuthenticator = $facebookAuthenticator;
        }
    */
    public function injectServiceAuthToken(ServiceAuthToken $serviceAuthToken) {
        $this->serviceAuthToken = $serviceAuthToken;
    }

    public function injectGlobalSession(IGlobalSession $globalSession) {
        $this->globalSession = $globalSession;
    }

    public function injectPasswordAuthenticator(PasswordAuthenticator $passwordAuthenticator) {
        $this->passwordAuthenticator = $passwordAuthenticator;
    }

    public function injectAccountManager(AccountManager $accountManager) {
        $this->accountManager = $accountManager;
    }

    public function injectMailTemplateFactory(MailTemplateFactory $mailTemplateFactory) {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function actionLogout() {
        $subdomainAuth = $this->globalParameters['subdomain']['auth'];
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
        $this->flashMessage(_("Byl jste odhlášen."), self::FLASH_SUCCESS);
        $this->loginBacklinkRedirect();
        $this->redirect("login");
    }

    public function actionLogin() {
        if ($this->isLoggedIn()) {
            $login = $this->getUser()->getIdentity();
            $this->loginBacklinkRedirect($login);
            $this->initialRedirect();
        } else {
            if ($this->flag == self::FLAG_SSO_PROBE) {
                $this->loginBacklinkRedirect();
            }
            if ($this->getParam(self::PARAM_REASON)) {
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
    }

    /*  public function actionFbLogin() {
          $this->setView('login'); // do not provide a special view
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
              $this->redirectUrl($fbUrl);
          }
      }*/

    public function actionRecover() {
        if ($this->isLoggedIn()) {
            //   $login = $this->getUser()->getIdentity();
            $this->initialRedirect();
        }
    }

    public function titleLogin() {
        $this->setTitle(_('Login'));
    }

    public function titleRecover() {
        $this->setTitle(_('Obnova hesla'));
    }

    public function renderLogin() {
        //  $this->template->fbUrl = $this->getFbLoginUrl();
    }

    public function renderRecover() {

    }

    /*private function getFbLoginUrl() {
        $fbUrl = $this->facebook->getLoginUrl(array(
            'scope' => 'email',
            'redirect_uri' => $this->link('//fbLogin'), // absolute
        ));
        return $fbUrl;
    }*/

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
     * @return Form
     */
    protected function createComponentLoginForm() {
        $form = new Form($this, 'loginForm');
        $form->addText('id', _('Přihlašovací jméno nebo email'))
            ->addRule(Form::FILLED, _('Zadejte přihlašovací jméno nebo emailovou adresu.'));

        $form->addPassword('password', _('Heslo'))
            ->addRule(Form::FILLED, _('Zadejte heslo.'));
        //$form->addCheckbox('remember', _('Zapamatovat si přihlášení'));

        $form->addSubmit('send', _('Přihlásit'));

        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));


        $form->onSuccess[] = callback($this, 'loginFormSubmitted');
        return $form;
    }

    /**
     * Password recover form.
     *
     * @return Form
     */
    protected function createComponentRecoverForm() {
        $form = new Form();
        $form->addText('id', _('Přihlašovací jméno nebo email'))
            ->addRule(Form::FILLED, _('Zadejte přihlašovací jméno nebo emailovou adresu.'));

        $form->addSubmit('send', _('Pokračovat'));

        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));

        $form->onSuccess[] = callback($this, 'recoverFormSubmitted');
        return $form;
    }

    public function loginFormSubmitted($form) {
        try {
            $this->user->login($form['id']->value, $form['password']->value);
            $login = $this->user->getIdentity();

            $this->loginBackLinkRedirect($login);
            $this->initialRedirect();
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        }
    }

    public function recoverFormSubmitted(Form $form) {
        $connection = $this->serviceAuthToken->getConnection();
        try {
            $values = $form->getValues();

            $connection->beginTransaction();
            /**
             * @var $login ModelLogin
             */
            $login = $this->passwordAuthenticator->findLogin($values['id']);
            $template = $this->mailTemplateFactory->createPasswordRecovery($this, $this->getLang());
            $this->accountManager->sendRecovery($template, $login);
            $email = Utils::cryptEmail($login->getPerson()->getInfo()->email);
            $this->flashMessage(sprintf(_('Na email %s byly poslány další instrukce k obnovení přístupu.'), $email), self::FLASH_SUCCESS);
            $connection->commit();
            $this->redirect('login');
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
            $connection->rollBack();
        } catch (RecoveryException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
            $connection->rollBack();
        } catch (SendFailedException $e) {
            $connection->rollBack();
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        }
    }

    private function loginBackLinkRedirect($login = null) {
        if (!$this->backlink) {
            return;
        }
        $this->restoreRequest($this->backlink);

        /* If it was't valid backlink serialization interpret it like a URL. */
        $url = new Url($this->backlink);
        $this->backlink = null;

        if (in_array($this->flag, array(self::FLAG_SSO_PROBE, self::FLAG_SSO_LOGIN))) {
            if ($login) {
                $gsid = $this->globalSession->getId();
                $expiration = $this->globalParameters['authentication']['sso']['tokenExpiration'];
                $until = DateTime::from($expiration);
                $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_SSO, $until, $gsid);
                $url->appendQuery(array(
                    LoginUserStorage::PARAM_SSO => LoginUserStorage::SSO_AUTHENTICATED,
                    TokenAuthenticator::PARAM_AUTH_TOKEN => $token->token
                ));
            } else {
                $url->appendQuery(array(
                    LoginUserStorage::PARAM_SSO => LoginUserStorage::SSO_UNAUTHENTICATED,
                ));
            }
        }

        if ($url->getHost()) { // this would indicate absolute URL
            if (in_array($url->getHost(), $this->globalParameters['authentication']['backlinkHosts'])) {
                $this->redirectUrl((string)$url, 303);
            } else {
                $this->flashMessage(sprintf(_('Nedovolený backlink %s.'), (string)$url), self::FLASH_ERROR);
            }
        }
    }

    /**
     * Fuck redirect!!!
     */
    private function initialRedirect() {
        $this->redirect('Chooser:default', [self::PARAM_DISPATCH => 1]);
    }

    public function getSelectedContestSymbol() {
        return null;
    }

    public function getNavRoot() {
        return null;
    }
}
