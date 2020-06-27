<?php

namespace FKSDB\Modules\CoreModule;

use Authentication\SSO\GlobalSession;
use FKSDB\Modules\Core\BasePresenter;
use Exception;
use FKSDB\Authentication\AccountManager;
use Authentication\LoginUserStorage;
use Authentication\PasswordAuthenticator;
use FKSDB\Authentication\RecoveryException;
use Authentication\TokenAuthenticator;
use FKSDB\Authentication\SSO\IGlobalSession;
use FKSDB\Authentication\SSO\ServiceSide\Authentication;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\UI\PageTitle;
use Mail\SendFailedException;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;
use Nette\Utils\DateTime;
use FKSDB\Utils\Utils;

/**
 * Class AuthenticationPresenter
 */

/**
 * Class AuthenticationPresenter
 */
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
     * @var ServiceAuthToken
     */
    private $serviceAuthToken;

    /**
     * todo check if type is persistent
     * @var GlobalSession
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
     * @var ServicePerson
     */
    protected $servicePerson;
    /**
     * @var string
     */
    private $login;
    /**
     * @param ServiceAuthToken $serviceAuthToken
     * @return void
     */
    public function injectServiceAuthToken(ServiceAuthToken $serviceAuthToken) {
        $this->serviceAuthToken = $serviceAuthToken;
    }

    /**
     * @param IGlobalSession $globalSession
     * @return void
     */
    public function injectGlobalSession(IGlobalSession $globalSession) {
        $this->globalSession = $globalSession;
    }

    /**
     * @param PasswordAuthenticator $passwordAuthenticator
     * @return void
     */
    public function injectPasswordAuthenticator(PasswordAuthenticator $passwordAuthenticator) {
        $this->passwordAuthenticator = $passwordAuthenticator;
    }

    /**
     * @param AccountManager $accountManager
     * @return void
     */
    public function injectAccountManager(AccountManager $accountManager) {
        $this->accountManager = $accountManager;
    }

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @throws AbortException
     * @throws InvalidLinkException
     */
    public function actionLogout() {
        $subDomainAuth = $this->globalParameters['subdomain']['auth'];
        $subDomain = $this->getParameter('subdomain');

        if ($subDomain != $subDomainAuth) {
            // local logout
            $this->getUser()->logout(true);

            // redirect to global logout
            $params = [
                'subdomain' => $subDomainAuth,
                self::PARAM_GSID => $this->globalSession->getId(),
            ];
            $url = $this->link('//this', $params);
            $this->redirectUrl($url);
        }
        // else: $subdomain == $subdomainAuth
        // -> check for the GSID parameter

        if ($this->isLoggedIn()) {
            $this->getUser()->logout(true); //clear identity
        } elseif ($this->getParameter(self::PARAM_GSID)) { // global session may exist but central login doesn't know it (e.g. expired its session)
            // We restart the global session with provided parameter.
            // This is secure as only harm an attacker can make to the user is to log him out.
            $this->globalSession->destroy();

            // If the GSID is valid, we'll obtain user's identity and log him out promptly.

            $this->globalSession->start($this->getParameter(self::PARAM_GSID));
            $this->getUser()->logout(true);
        }
        $this->flashMessage(_('Byl jste odhlášen.'), self::FLASH_SUCCESS);
        $this->loginBackLinkRedirect();
        $this->redirect('login');
    }

    /**
     * @throws AbortException
     */
    public function actionLogin() {
        if ($this->isLoggedIn()) {
            /**
             * @var ModelLogin $login
             */
            $login = $this->getUser()->getIdentity();
            $this->loginBackLinkRedirect($login);
            $this->initialRedirect();
        } else {
            if ($this->flag == self::FLAG_SSO_PROBE) {
                $this->loginBackLinkRedirect();
            }
            if ($this->getParameter(self::PARAM_REASON)) {
                switch ($this->getParameter(self::PARAM_REASON)) {
                    case self::REASON_TIMEOUT:
                        $this->flashMessage(_('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.'), self::FLASH_INFO);
                        break;
                    case self::REASON_AUTH:
                        $this->flashMessage(_('Stránka požaduje přihlášení.'), self::FLASH_ERROR);
                        break;
                }
            }
            $this->login = $this->getParameter('login');

        }
    }

    /**
     * @throws AbortException
     */
    public function actionRecover() {
        if ($this->isLoggedIn()) {
            $this->initialRedirect();
        }
    }

    public function titleLogin() {
        $this->setPageTitle(new PageTitle(_('Login')));
    }

    public function titleRecover() {
        $this->setPageTitle(new PageTitle(_('Obnova hesla')));
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
     * @return Form
     */
    protected function createComponentLoginForm() {
        $form = new Form($this, 'loginForm');
        $form->addText('id', _('Přihlašovací jméno nebo email'))
            ->addRule(Form::FILLED, _('Zadejte přihlašovací jméno nebo emailovou adresu.'))
            ->getControlPrototype()->addAttributes([
                'class' => 'top form-control',
                'autofocus' => true,
                'placeholder' => _('Přihlašovací jméno nebo email'),
                'autocomplete' => 'username',
            ]);
        $form->addPassword('password', _('Heslo'))
            ->addRule(Form::FILLED, _('Zadejte heslo.'))->getControlPrototype()->addAttributes([
                'class' => 'bottom mb-3 form-control',
                'placeholder' => _('Heslo'),
                'autocomplete' => 'current-password',
            ]);
        $form->addSubmit('send', _('Přihlásit'));
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        $form->onSuccess[] = function (Form $form) {
            return $this->loginFormSubmitted($form);
        };
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

        $form->onSuccess[] = function (Form $form) {
            $this->recoverFormSubmitted($form);
        };
        return $form;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function loginFormSubmitted(Form $form) {
        try {
            $this->user->login($form['id']->value, $form['password']->value);
            /** @var ModelLogin $login */
            $login = $this->user->getIdentity();
            $this->loginBackLinkRedirect($login);
            $this->initialRedirect();
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
        }
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws Exception
     */
    private function recoverFormSubmitted(Form $form) {
        $connection = $this->serviceAuthToken->getConnection();
        try {
            $values = $form->getValues();

            $connection->beginTransaction();
            $login = $this->passwordAuthenticator->findLogin($values['id']);
            $this->accountManager->sendRecovery($login, $login->getPerson()->getPreferredLang() ?: $this->getLang());
            $email = Utils::cryptEmail($login->getPerson()->getInfo()->email);
            $this->flashMessage(sprintf(_('Na email %s byly poslány další instrukce k obnovení přístupu.'), $email), self::FLASH_SUCCESS);
            $connection->commit();
            $this->redirect('login');
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
            $connection->rollBack();
        } catch (RecoveryException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
            $connection->rollBack();
        } catch (SendFailedException $exception) {
            $connection->rollBack();
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
        }
    }

    /**
     * @param null $login
     * @throws AbortException
     * @throws Exception
     */
    private function loginBackLinkRedirect($login = null) {
        if (!$this->backlink) {
            return;
        }
        $this->restoreRequest($this->backlink);

        /* If it was't valid backLink serialization interpret it like a URL. */
        $url = new Url($this->backlink);
        $this->backlink = null;

        if (in_array($this->flag, [self::FLAG_SSO_PROBE, self::FLAG_SSO_LOGIN])) {
            if ($login) {
                $globalSessionId = $this->globalSession->getId();
                $expiration = $this->globalParameters['authentication']['sso']['tokenExpiration'];
                $until = DateTime::from($expiration);
                $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_SSO, $until, $globalSessionId);
                $url->appendQuery([
                    LoginUserStorage::PARAM_SSO => LoginUserStorage::SSO_AUTHENTICATED,
                    TokenAuthenticator::PARAM_AUTH_TOKEN => $token->token,
                ]);
            } else {
                $url->appendQuery([
                    LoginUserStorage::PARAM_SSO => LoginUserStorage::SSO_UNAUTHENTICATED,
                ]);
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
     * @throws AbortException
     */
    private function initialRedirect() {
        if ($this->backlink) {
            $this->restoreRequest($this->backlink);
        }
        $this->redirect(':Core:Dispatch:');
    }

    public function renderLogin() {
        $this->template->login = $this->login;
    }

    protected function beforeRender() {
        $this->getPageStyleContainer()->styleId = 'login';
        $this->getPageStyleContainer()->mainContainerClassName = '';
        parent::beforeRender();
    }
}
