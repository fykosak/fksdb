<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Authentication\GoogleAuthenticator;
use FKSDB\Authentication\InactiveLoginException;
use FKSDB\Authentication\InvalidCredentialsException;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\Modules\Core\BasePresenter;
use Exception;
use FKSDB\Authentication\AccountManager;
use FKSDB\Authentication\LoginUserStorage;
use FKSDB\Authentication\PasswordAuthenticator;
use FKSDB\Authentication\RecoveryException;
use FKSDB\Authentication\TokenAuthenticator;
use FKSDB\Authentication\SSO\IGlobalSession;
use FKSDB\Authentication\SSO\ServiceSide\Authentication;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\UI\PageTitle;
use FKSDB\Mail\SendFailedException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Controls\TextInput;
use Nette\Http\SessionSection;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;
use Nette\Utils\DateTime;
use FKSDB\Utils\Utils;

/**
 * Class AuthenticationPresenter
 */
final class AuthenticationPresenter extends BasePresenter {

    public const PARAM_GSID = 'gsid';
    /** @const Indicates that page is accessed via dispatch from the login page. */
    public const PARAM_DISPATCH = 'dispatch';
    /** @const Reason why the user has been logged out. */
    public const PARAM_REASON = 'reason';
    /** @const Various modes of authentication. */
    public const PARAM_FLAG = 'flag';
    /** @const User is shown the login form if he's not authenticated. */
    public const FLAG_SSO_LOGIN = Authentication::FLAG_SSO_LOGIN;
    /** @const Only check of authentication with subsequent backlink redirect. */
    public const FLAG_SSO_PROBE = 'ssop';
    public const REASON_TIMEOUT = '1';
    public const REASON_AUTH = '2';

    /** @persistent */
    public $backlink = '';

    /** @persistent */
    public $flag;

    private ServiceAuthToken $serviceAuthToken;
    private IGlobalSession $globalSession;
    private PasswordAuthenticator $passwordAuthenticator;
    private AccountManager $accountManager;
    private Google $googleProvider;
    private GoogleAuthenticator $googleAuthenticator;

    final public function injectTernary(
        ServiceAuthToken $serviceAuthToken,
        IGlobalSession $globalSession,
        PasswordAuthenticator $passwordAuthenticator,
        AccountManager $accountManager,
        GoogleAuthenticator $googleAuthenticator
    ): void {
        $this->serviceAuthToken = $serviceAuthToken;
        $this->globalSession = $globalSession;
        $this->passwordAuthenticator = $passwordAuthenticator;
        $this->accountManager = $accountManager;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    public function titleLogin(): void {
        $this->setPageTitle(new PageTitle(_('Login')));
    }

    public function titleRecover(): void {
        $this->setPageTitle(new PageTitle(_('Password recovery')));
    }

    /**
     * @throws AbortException
     * @throws InvalidLinkException
     */
    public function actionLogout(): void {
        $subDomainAuth = $this->getContext()->getParameters()['subdomain']['auth'];
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
        $this->flashMessage(_('You were logged out.'), self::FLASH_SUCCESS);
        $this->loginBackLinkRedirect();
        $this->redirect('login');
    }

    /**
     * @throws AbortException
     * @throws BadTypeException
     */
    public function actionLogin(): void {
        if ($this->isLoggedIn()) {
            /** @var ModelLogin $login */
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
                        $this->flashMessage(_('You\'ve been logged out due to inactivity.'), self::FLASH_INFO);
                        break;
                    case self::REASON_AUTH:
                        $this->flashMessage(_('You must be logged in to continue.'), self::FLASH_ERROR);
                        break;
                }
            }
            /** @var FormControl $formControl */
            $formControl = $this->getComponent('loginForm');
            $login = $this->getParameter('login');
            if ($login) {
                $formControl->getForm()->setDefaults(['id' => $login]);
                /** @var TextInput $input */
                $input = $formControl->getForm()->getComponent('id');
                /* $input->setDisabled()
                     ->setOmitted(false)
                     ->setDefaultValue($login);*/
            }
        }
    }

    /**
     * @throws AbortException
     */
    public function actionRecover(): void {
        if ($this->isLoggedIn()) {
            $this->initialRedirect();
        }
    }

    /**
     * This workaround is here because LoginUser storage
     * returns false when only global login exists.
     * False is return in order to AuthenticatedPresenter to correctly login the user.
     *
     * @return bool
     */
    private function isLoggedIn(): bool {
        return $this->getUser()->isLoggedIn() || isset($this->globalSession[IGlobalSession::UID]);
    }

    /*     * ******************* components ****************************** */

    /**
     * Login form component factory.
     * @return Form
     */
    protected function createComponentLoginForm(): Form {
        $form = new Form($this, 'loginForm');
        $form->addText('id', _('Login or e-mail'))
            ->addRule(Form::FILLED, _('Insert login or email address.'))
            ->getControlPrototype()->addAttributes([
                'class' => 'top form-control',
                'autofocus' => true,
                'placeholder' => _('Login or e-mail'),
                'autocomplete' => 'username',
            ]);
        $form->addPassword('password', _('Password'))
            ->addRule(Form::FILLED, _('Type password.'))->getControlPrototype()->addAttributes([
                'class' => 'bottom mb-3 form-control',
                'placeholder' => _('Password'),
                'autocomplete' => 'current-password',
            ]);
        $form->addSubmit('send', _('Log in'));
        $form->addProtection(_('The form has expired. Please send it again.'));
        $form->onSuccess[] = function (Form $form) {
            $this->loginFormSubmitted($form);
        };
        return $form;
    }

    /**
     * Password recover form.
     *
     * @return Form
     */
    protected function createComponentRecoverForm(): Form {
        $form = new Form();
        $form->addText('id', _('Login or e-mail address'))
            ->addRule(Form::FILLED, _('Insert login or email address.'));

        $form->addSubmit('send', _('Continue'));

        $form->addProtection(_('The form has expired. Please send it again.'));

        $form->onSuccess[] = function (Form $form) {
            $this->recoverFormSubmitted($form);
        };
        return $form;
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws Exception
     */
    private function loginFormSubmitted(Form $form): void {
        $values = $form->getValues();
        try {
            // TODO use form->getValues()
            $this->user->login($values['id'], $values['password']);
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
     * @return void
     * @throws AbortException
     * @throws UnsupportedLanguageException
     */
    private function recoverFormSubmitted(Form $form): void {
        $connection = $this->serviceAuthToken->getConnection();
        try {
            $values = $form->getValues();

            $connection->beginTransaction();
            $login = $this->passwordAuthenticator->findLogin($values['id']);
            $this->accountManager->sendRecovery($login, $login->getPerson()->getPreferredLang() ?: $this->getLang());
            $email = Utils::cryptEmail($login->getPerson()->getInfo()->email);
            $this->flashMessage(sprintf(_('Further instructions for the recovery have been sent to %s.'), $email), self::FLASH_SUCCESS);
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
     * @param ModelLogin|null $login
     * @return void
     */
    private function loginBackLinkRedirect($login = null): void {
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
                $expiration = $this->getContext()->getParameters()['authentication']['sso']['tokenExpiration'];
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
            if (in_array($url->getHost(), $this->getContext()->getParameters()['authentication']['backlinkHosts'])) {
                $this->redirectUrl((string)$url, 303);
            } else {
                $this->flashMessage(sprintf(_('Backlink %s not allowed'), (string)$url), self::FLASH_ERROR);
            }
        }
    }

    /**
     * @return void
     * @throws InactiveLoginException
     * @throws InvalidCredentialsException
     * @throws AuthenticationException
     * @throws InvalidLinkException
     */
    public function actionGoogle(): void {
        if ($this->getGoogleSection()->state !== $this->getGoogleOAuth2Provider()->getState()) {
            $this->flashMessage(_('Invalid CSRF token'), self::FLASH_ERROR);
            $this->redirect('login');
        }
        try {
            $token = $this->getGoogleOAuth2Provider()->getAccessToken('authorization_code', [
                'code' => $this->getParameter('code'),
            ]);
            $ownerDetails = $this->getGoogleOAuth2Provider()->getResourceOwner($token);
            $login = $this->googleAuthenticator->authenticate($ownerDetails->toArray());
            $this->getUser()->login($login);
            $this->initialRedirect();
        } catch (IdentityProviderException $exception) {
            $this->flashMessage(_('Error'), self::FLASH_ERROR);
            $this->redirect('login');
        }
    }

    /**
     * @throws AbortException
     * @throws Exception
     */
    public function handleGoogle(): void {
        $this->getGoogleSection()->state = $this->getGoogleOAuth2Provider()->getState();
        $this->redirectUrl($this->getGoogleOAuth2Provider()->getAuthorizationUrl());
    }

    /**
     * @throws AbortException
     */
    private function initialRedirect(): void {
        if ($this->backlink) {
            $this->restoreRequest($this->backlink);
        }
        $this->redirect(':Core:Dispatch:');
    }

    protected function beforeRender(): void {
        $this->getPageStyleContainer()->styleId = 'login';
        $this->getPageStyleContainer()->mainContainerClassNames = [];
        parent::beforeRender();
    }

    public function getGoogleSection(): SessionSection {
        return $this->getSession()->getSection('google-oauth2state');
    }

    /**
     * @return Google
     * @throws InvalidLinkException
     */
    protected function getGoogleOAuth2Provider(): Google {
        $params = $this->getContext()->getParameters()['googleOAuth2'];
        if (!isset($this->googleProvider)) {
            $this->googleProvider = new Google([
                'clientId' => $params['clientId'],    // The client ID assigned to you by the provider
                'clientSecret' => $params['clientSecret'],
                'scope' => 'openid email',// The client password assigned to you by the provider
                'redirectUri' => $this->link('//google', ['bc' => null]),
            ]);
        }
        return $this->googleProvider;
    }
}
