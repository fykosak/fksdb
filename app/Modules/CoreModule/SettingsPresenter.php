<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\PreferredLangFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueLogin;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\UI\PageTitle;
use FKSDB\Models\Utils\FormUtils;
use Nette\Application\UI\Form;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SettingsPresenter extends BasePresenter {

    /** show field pair for setting a password */
    public const SHOW_PASSWORD = 0x2;
    /** show field for the old password */
    public const VERIFY_OLD_PASSWORD = 0x4;
    /** require nonempty (new) password */
    public const REQUIRE_PASSWORD = 0x8;

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

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Settings'), 'fa fa-cogs'));
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    public function actionDefault(): void {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();

        $defaults = [
            self::CONT_LOGIN => $login->toArray(),
        ];
        /** @var FormControl $control */
        $control = $this->getComponent('settingsForm');
        $control->getForm()->setDefaults($defaults);
    }

    final public function renderDefault(): void {
        if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN)) {
            $this->flashMessage(_('Set up new password.'), self::FLASH_WARNING);
        }

        if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY)) {
            $this->flashMessage(_('Set up new password.'), self::FLASH_WARNING);
        }
    }

    protected function createComponentPreferredLangForm(): PreferredLangFormComponent {
        return new PreferredLangFormComponent($this->getContext(), $this->getUser()->getIdentity()->getPerson());
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentSettingsForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $tokenAuthentication =
            $this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_INITIAL_LOGIN) ||
            $this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_RECOVERY);

        $group = $form->addGroup(_('Authentication'));

        if ($tokenAuthentication) {
            $options = self::SHOW_PASSWORD | self::REQUIRE_PASSWORD;
        } elseif (!$login->hash) {
            $options = self::SHOW_PASSWORD;
        } else {
            $options = self::SHOW_PASSWORD | self::VERIFY_OLD_PASSWORD;
        }
        $loginContainer = $this->createLogin($options, $group, function (BaseControl $baseControl) use ($login): bool {
            $uniqueLogin = new UniqueLogin($this->loginService);
            $uniqueLogin->setIgnoredLogin($login);

            $uniqueEmail = new UniqueEmail($this->servicePersonInfo);
            $uniqueEmail->setIgnoredPerson($login->getPerson());

            return $uniqueEmail($baseControl) && $uniqueLogin($baseControl);
        });
        $form->addComponent($loginContainer, self::CONT_LOGIN);
        /** @var TextInput|null $oldPasswordControl */
        $oldPasswordControl = $loginContainer->getComponent('old_password', false);
        if ($oldPasswordControl) {
            $oldPasswordControl
                ->addCondition(Form::FILLED)
                ->addRule(function (BaseControl $control) use ($login): bool {
                    $hash = PasswordAuthenticator::calculateHash($control->getValue(), $login);
                    return $hash == $login->hash;
                }, _('Incorrect old password.'));
        }

        $form->setCurrentGroup();

        $form->addSubmit('send', _('Save'));

        $form->onSuccess[] = function (Form $form) {
            $this->handleSettingsFormSuccess($form);
        };
        return $control;
    }

    private function createLogin(int $options = 0, ?ControlGroup $group = null, ?callable $loginRule = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $login = $container->addText('login', _('Username'));
        $login->setHtmlAttribute('autocomplete', 'username');

        if ($loginRule) {
            $login->addRule($loginRule, _('This username is already taken.'));
        }

        if ($options & self::SHOW_PASSWORD) {
            if ($options & self::VERIFY_OLD_PASSWORD) {
                $container->addPassword('old_password', _('Old password'))->setHtmlAttribute('autocomplete', 'current-password');
            }
            $newPwd = $container->addPassword('password', _('Password'));
            $newPwd->setHtmlAttribute('autocomplete', 'new-password');
            $newPwd->addCondition(Form::FILLED)->addRule(Form::MIN_LENGTH, _('The password must have at least %d characters.'), 6);

            if ($options & self::VERIFY_OLD_PASSWORD) {
                $newPwd->addConditionOn($container->getComponent('old_password'), Form::FILLED)
                    ->addRule(Form::FILLED, _('It is necessary to set a new password.'));
            } elseif ($options & self::REQUIRE_PASSWORD) {
                $newPwd->addRule(Form::FILLED, _('Password cannot be empty.'));
            }

            $container->addPassword('password_verify', _('Password (verification)'))
                ->addRule(Form::EQUAL, _('The submitted passwords do not match.'), $newPwd)
                ->setHtmlAttribute('autocomplete', 'new-password');
        }

        return $container;
    }

    /**
     * @param Form $form
     * @throws ModelException
     */
    private function handleSettingsFormSuccess(Form $form): void {
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

        $this->loginService->updateModel2($login, $loginData);

        $this->flashMessage(_('User information has been saved.'), self::FLASH_SUCCESS);
        if ($tokenAuthentication) {
            $this->flashMessage(_('Password changed.'), self::FLASH_SUCCESS);
            $this->tokenAuthenticator->disposeAuthToken(); // from now on same like password authentication
        }
        $this->redirect('this');
    }
}
