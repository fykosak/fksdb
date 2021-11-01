<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\PreferredLangFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueLogin;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Application\UI\Form;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

class SettingsPresenter extends BasePresenter
{

    public const CONT_LOGIN = 'login';

    private ServiceLogin $loginService;
    private ServicePersonInfo $servicePersonInfo;

    final public function injectQuarterly(
        ServiceLogin $loginService,
        ServicePersonInfo $servicePersonInfo
    ): void {
        $this->loginService = $loginService;
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Settings'), 'fa fa-cogs');
    }

    /**
     * @throws BadTypeException
     */
    public function actionDefault(): void
    {
        /** @var ModelLogin $login */
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
        if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN)) {
            $this->flashMessage(_('Set up new password.'), self::FLASH_WARNING);
        }

        if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY)) {
            $this->flashMessage(_('Set up new password.'), self::FLASH_WARNING);
        }
    }

    protected function createComponentPreferredLangForm(): PreferredLangFormComponent
    {
        return new PreferredLangFormComponent($this->getContext(), $this->getUser()->getIdentity()->getPerson());
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentSettingsForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $tokenAuthentication =
            $this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN) ||
            $this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY);

        $group = $form->addGroup(_('Authentication'));
        $rule = function (BaseControl $baseControl) use ($login): bool {
            $uniqueLogin = new UniqueLogin($this->loginService);
            $uniqueLogin->setIgnoredLogin($login);

            $uniqueEmail = new UniqueEmail($this->servicePersonInfo);
            $uniqueEmail->setIgnoredPerson($login->getPerson());

            return $uniqueEmail($baseControl) && $uniqueLogin($baseControl);
        };
        $loginContainer = $this->createLogin(
            $group,
            $rule,
            true,
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
                        $hash = PasswordAuthenticator::calculateHash($control->getValue(), $login);
                        return $hash == $login->hash;
                    },
                    _('Incorrect old password.')
                );
        }

        $form->setCurrentGroup();

        $form->addSubmit('send', _('Save'));

        $form->onSuccess[] = function (Form $form) {
            $this->handleSettingsFormSuccess($form);
        };
        return $control;
    }

    private function createLogin(
        ControlGroup $group,
        callable $loginRule,
        bool $showPassword = true,
        bool $verifyOldPassword = false,
        bool $requirePassword = false
    ): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $login = $container->addText('login', _('Username'));
        $login->setHtmlAttribute('autocomplete', 'username');

        if ($loginRule) {
            $login->addRule($loginRule, _('This username is already taken.'));
        }

        if ($showPassword) {
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
        }

        return $container;
    }

    /**
     * @throws ModelException
     */
    private function handleSettingsFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $tokenAuthentication =
            $this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN) ||
            $this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY);
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();

        $loginData = FormUtils::emptyStrToNull($values[self::CONT_LOGIN], true);
        if ($loginData['password']) {
            $loginData['hash'] = $login->createHash($loginData['password']);
        }

        $this->loginService->updateModel($login, $loginData);

        $this->flashMessage(_('User information has been saved.'), self::FLASH_SUCCESS);
        if ($tokenAuthentication) {
            $this->flashMessage(_('Password changed.'), self::FLASH_SUCCESS);
            $this->tokenAuthenticator->disposeAuthToken(); // from now on same like password authentication
        }
        $this->redirect('this');
    }
}
