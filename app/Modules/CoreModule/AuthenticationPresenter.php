<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Login\LoginForm;
use FKSDB\Components\Controls\Recovery\RecoveryForm;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\Authentication\GoogleAuthenticator;
use FKSDB\Models\Authentication\Provider\GoogleProvider;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use Nette\Http\SessionSection;
use Nette\Security\AuthenticationException;
use Nette\Security\UserStorage;

final class AuthenticationPresenter extends BasePresenter
{
    /** @const Reason why the user has been logged out. */
    public const PARAM_REASON = 'reason';
    /** @persistent */
    public ?string $backlink = '';
    private Google $googleProvider;
    private GoogleAuthenticator $googleAuthenticator;

    final public function injectTernary(
        GoogleAuthenticator $googleAuthenticator,
        GoogleProvider $googleProvider
    ): void {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->googleProvider = $googleProvider;
    }

    public function requiresLogin(): bool
    {
        return false;
    }

    public function authorizedLogin(): bool
    {
        return true;
    }

    public function titleLogin(): PageTitle
    {
        return new PageTitle(null, _('Login'), 'fas fa-right-to-bracket');
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

    public function authorizedLogout(): bool
    {
        return true;
    }

    /**
     * @throws NotImplementedException
     */
    public function titleLogout(): PageTitle
    {
        throw new NotImplementedException();
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

    public function authorizedRecover(): bool
    {
        return true;
    }

    public function titleRecover(): PageTitle
    {
        return new PageTitle(null, _('Password recovery'), 'fas fa-hammer');
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
    private function initialRedirect(): void
    {
        if ($this->backlink) {
            $this->restoreRequest($this->backlink);
        }
        $this->redirect(':Core:Dispatch:');
    }

    public function authorizedGoogle(): bool
    {
        return true;
    }

    public function titleGoogle(): PageTitle
    {
        return new PageTitle(null, _('Google'), 'fas fa-hammer');
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
            $ownerDetails = $this->googleProvider->getResourceOwner($token); // @phpstan-ignore-line
            $login = $this->googleAuthenticator->authenticate($ownerDetails->toArray()); // @phpstan-ignore-line
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
    protected function createComponentLoginForm(): LoginForm
    {
        return new LoginForm($this->getContext(), fn() => $this->initialRedirect());
    }

    /**
     * Password recover form.
     */
    protected function createComponentRecoverForm(): RecoveryForm
    {
        return new RecoveryForm($this->getContext());
    }

    protected function getStyleId(): string
    {
        return 'login';
    }
}
