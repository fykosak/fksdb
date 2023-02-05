<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class SettingsPresenter extends BasePresenter
{
    public const CONT_LOGIN = 'login';

    private LoginService $loginService;

    final public function injectQuarterly(LoginService $loginService): void
    {
        $this->loginService = $loginService;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Settings'), 'fa fa-cogs');
    }

    /**
     * @throws BadTypeException
     */
    public function actionDefault(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();

        $defaults = [
            self::CONT_LOGIN => $login->toArray(),
        ];
        /** @var FormControl $control */
        $control = $this->getComponent('settingsForm');
        $control->getForm()->setDefaults($defaults);
    }

    final public function renderDefault(): void
    {
        if (
            $this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::Recovery) ||
            $this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::InitialLogin)
        ) {
            $this->flashMessage(_('Set up new password.'), Message::LVL_WARNING);
        }
    }


    /**
     * @throws BadTypeException
     */
    protected function createComponentSettingsForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        $tokenAuthentication =
            $this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::InitialLogin) ||
            $this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::Recovery);

        $group = $form->addGroup(_('Authentication'));
        $loginContainer = $this->createLogin(
            $group,
            $login->hash && (!$tokenAuthentication),
            $tokenAuthentication
        );
        $form->addComponent($loginContainer, self::CONT_LOGIN);
        /** @var TextInput|null $oldPasswordControl */
        $oldPasswordControl = $loginContainer->getComponent('old_password', false);
        if ($oldPasswordControl) {
            $oldPasswordControl
                ->addCondition(Form::FILLED)
                ->addRule(
                    function (BaseControl $control) use ($login): bool {
                        $hash = $login->calculateHash($control->getValue());
                        return $hash == $login->hash;
                    },
                    _('Incorrect old password.')
                );
        }

        $form->setCurrentGroup();
        $form->addSubmit('send', _('Save'));
        $form->onSuccess[] = fn(\Nette\Application\UI\Form $form) => $this->handleSettingsFormSuccess($form);
        return $control;
    }

    private function createLogin(
        ControlGroup $group,
        bool $verifyOldPassword = false,
        bool $requirePassword = false
    ): ContainerWithOptions {
        $container = new ContainerWithOptions($this->getContext());
        $container->setCurrentGroup($group);

        if ($verifyOldPassword) {
            $container->addPassword('old_password', _('Old password'))->setHtmlAttribute(
                'autocomplete',
                'current-password'
            );
        }
        $newPwd = $container->addPassword('password', _('Password'));
        $newPwd->setHtmlAttribute('autocomplete', 'new-password');
        $newPwd->addCondition(Form::FILLED)->addRule(
            Form::MIN_LENGTH,
            _('The password must have at least %d characters.'),
            6
        );

        if ($verifyOldPassword) {
            $newPwd->addConditionOn($container->getComponent('old_password'), Form::FILLED)
                ->addRule(Form::FILLED, _('It is necessary to set a new password.'));
        } elseif ($requirePassword) {
            $newPwd->addRule(Form::FILLED, _('Password cannot be empty.'));
        }

        $container->addPassword('password_verify', _('Password (verification)'))
            ->addRule(Form::EQUAL, _('The submitted passwords do not match.'), $newPwd)
            ->setHtmlAttribute('autocomplete', 'new-password');

        return $container;
    }

    /**
     * @throws ModelException
     */
    private function handleSettingsFormSuccess(\Nette\Application\UI\Form $form): void
    {
        $values = $form->getValues();
        $tokenAuthentication =
            $this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::InitialLogin) ||
            $this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::Recovery);
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();

        $loginData = FormUtils::emptyStrToNull2($values[self::CONT_LOGIN]);
        if ($loginData['password']) {
            $loginData['hash'] = $login->calculateHash($loginData['password']);
        }

        $this->loginService->storeModel($loginData, $login);

        $this->flashMessage(_('User information has been saved.'), Message::LVL_SUCCESS);
        if ($tokenAuthentication) {
            $this->flashMessage(_('Password changed.'), Message::LVL_SUCCESS);
            $this->tokenAuthenticator->disposeAuthToken(); // from now on same like password authentication
        }
        $this->redirect('this');
    }
}
