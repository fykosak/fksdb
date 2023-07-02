<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Authentication\Exceptions\NoLoginException;
use FKSDB\Models\Authentication\Exceptions\RecoveryException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\Authentication\GoogleAuthenticator;
use FKSDB\Models\Authentication\Provider\GoogleProvider;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\Utils\Utils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use Nette\Application\UI\Form;
use Nette\Http\SessionSection;
use Nette\Security\AuthenticationException;
use Nette\Security\UserStorage;

final class AuthenticationPresenter extends BasePresenter
{

    /** @const Reason why the user has been logged out. */
    public const PARAM_REASON = 'reason';
    /** @persistent */
    public ?string $backlink = '';
    private AuthTokenService $authTokenService;
    private AccountManager $accountManager;
    private Google $googleProvider;
    private GoogleAuthenticator $googleAuthenticator;

    final public function injectTernary(
        AuthTokenService $authTokenService,
        AccountManager $accountManager,
        GoogleAuthenticator $googleAuthenticator,
        GoogleProvider $googleProvider
    ): void {
        $this->authTokenService = $authTokenService;
        $this->accountManager = $accountManager;
        $this->googleAuthenticator = $googleAuthenticator;
        $this->googleProvider = $googleProvider;
    }

    public function authorizedLogin(): bool
    {
        return true;
    }

    public function authorizedLogout(): bool
    {
        return true;
    }

    public function authorizedRecover(): bool
    {
        return true;
    }

    public function requiresLogin(): bool
    {
        return false;
    }

    public function titleLogin(): PageTitle
    {
        return new PageTitle(null, _('Login'), 'fas fa-right-to-bracket');
    }

    public function titleRecover(): PageTitle
    {
        return new PageTitle(null, _('Password recovery'), 'fas fa-hammer');
    }

    /**
     * @throws \Exception
     */
    public function actionLogout(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout(true); //clear identity
        }
        $this->flashMessage(_('You were logged out.'), Message::LVL_SUCCESS);
        $this->redirect('login');
    }

    /**
     * @throws \Exception
     */
    public function actionLogin(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->initialRedirect();
        } else {
            if ($this->getParameter(self::PARAM_REASON)) {
                switch ($this->getParameter(self::PARAM_REASON)) {
                    case UserStorage::LOGOUT_INACTIVITY:
                        $this->flashMessage(_('You\'ve been logged out due to inactivity.'), Message::LVL_INFO);
                        break;
                    case UserStorage::LOGOUT_MANUAL:
                        $this->flashMessage(_('You must be logged in to continue.'), Message::LVL_ERROR);
                        break;
                }
            }
            /** @var FormControl $formControl */
            $formControl = $this->getComponent('loginForm');
            $login = $this->getParameter('login');
            if ($login) {
                $formControl->getForm()->setDefaults(['id' => $login]);
                $formControl->getForm()->getComponent('id');
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function initialRedirect(): void
    {
        if ($this->backlink) {
            $this->restoreRequest($this->backlink);
        }
        $this->redirect(':Core:Dispatch:');
    }

    /**
     * @throws \Exception
     */
    public function actionRecover(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->initialRedirect();
        }
    }

    /**
     * @throws \Exception
     */
    public function actionGoogle(): void
    {
        if ($this->getGoogleSection()->state !== $this->getParameter('state')) {
            $this->flashMessage(_('Invalid CSRF token'), Message::LVL_ERROR);
            $this->redirect('login');
        }
        try {
            $token = $this->googleProvider->getAccessToken(
                'authorization_code',
                [
                    'code' => $this->getParameter('code'),
                ]
            );
            $ownerDetails = $this->googleProvider->getResourceOwner($token);
            $login = $this->googleAuthenticator->authenticate($ownerDetails->toArray());
            $this->getUser()->login($login);
            $this->initialRedirect();
        } catch (UnknownLoginException $exception) {
            $this->flashMessage(_('No account is associated with this profile'), Message::LVL_ERROR);
            $this->redirect('login');
        } catch (IdentityProviderException | AuthenticationException $exception) {
            $this->flashMessage(_('Error'), Message::LVL_ERROR);
            $this->redirect('login');
        }
    }

    public function getGoogleSection(): SessionSection
    {
        return $this->getSession()->getSection('google-oauth2state');
    }

    /**
     * @throws \Exception
     */
    public function handleGoogle(): void
    {
        $url = $this->googleProvider->getAuthorizationUrl();
        $this->getGoogleSection()->state = $this->googleProvider->getState();
        $this->redirectUrl($url);
    }

    /**
     * Login form component factory.
     */
    protected function createComponentLoginForm(): Form
    {
        $form = new Form($this, 'loginForm');
        $form->addText('id', _('Login or e-mail'))
            ->addRule(Form::FILLED, _('Insert login or email address.'))
            ->getControlPrototype()->addAttributes(
                [
                    'class' => 'top form-control',
                    'autofocus' => true,
                    'placeholder' => _('Login or e-mail'),
                    'autocomplete' => 'username',
                ]
            );
        $form->addPassword('password', _('Password'))
            ->addRule(Form::FILLED, _('Type password.'))->getControlPrototype()->addAttributes(
                [
                    'class' => 'bottom mb-3 form-control',
                    'placeholder' => _('Password'),
                    'autocomplete' => 'current-password',
                ]
            );
        $form->addSubmit('send', _('Log in'));
        $form->addProtection(_('The form has expired. Please send it again.'));
        $form->onSuccess[] = fn(Form $form) => $this->loginFormSubmitted($form);

        return $form;
    }

    /**
     * @throws \Exception
     */
    private function loginFormSubmitted(Form $form): void
    {
        $values = $form->getValues();
        try {
            $this->getUser()->login($values['id'], $values['password']);
            /** @var LoginModel $login */
            $this->initialRedirect();
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    /**
     * Password recover form.
     */
    protected function createComponentRecoverForm(): Form
    {
        $form = new Form();
        $form->addText('id', _('Login or e-mail address'))
            ->addRule(Form::FILLED, _('Insert login or email address.'));

        $form->addSubmit('send', _('Continue'));

        $form->addProtection(_('The form has expired. Please send it again.'));

        $form->onSuccess[] = fn(Form $form) => $this->recoverFormSubmitted($form);

        return $form;
    }

    /**
     * @throws BadTypeException
     */
    private function recoverFormSubmitted(Form $form): void
    {
        $connection = $this->authTokenService->explorer->getConnection();
        try {
            $values = $form->getValues();

            $connection->beginTransaction();
            try {
                $login = $this->passwordAuthenticator->findLogin($values['id']);
            } catch (NoLoginException $exception) {
                $person = $this->passwordAuthenticator->findPersonByEmail($values['id']);
                $login = $this->accountManager->createLogin($person);
            }

            $this->accountManager->sendRecovery($login, $login->person->getPreferredLang() ?? $this->getLang());
            $email = Utils::cryptEmail($login->person->getInfo()->email);
            $this->flashMessage(
                sprintf(_('Further instructions for the recovery have been sent to %s.'), $email),
                Message::LVL_SUCCESS
            );
            $connection->commit();
            $this->redirect('login');
        } catch (AuthenticationException | RecoveryException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $connection->rollBack();
        }
    }

    protected function getStyleId(): string
    {
        return 'login';
    }
}
