<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueLogin;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @property-read LoginModel $model
 */
class LoginFomComponent extends EntityFormComponent
{
    private LoginService $loginService;
    private const CONTAINER = 'login';

    public function __construct(Container $container, LoginModel $model, private readonly bool $verifyOld = false)
    {
        parent::__construct($container, $model);
    }

    public function inject(LoginService $loginService): void
    {
        $this->loginService = $loginService;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer();
        $login = $container->addText('login', _('Username'));
        $login->setHtmlAttribute('autocomplete', 'username');
        $login->addRule(function (BaseControl $baseControl): bool {
            $uniqueLogin = new UniqueLogin($this->getContext());
            $uniqueLogin->setIgnoredLogin($this->model);

            $uniqueEmail = new UniqueEmail($this->getContext());
            $uniqueEmail->setIgnoredPerson($this->model->person);

            return $uniqueEmail($baseControl) && $uniqueLogin($baseControl);
        }, _('This username is already taken.'));

        if ($this->verifyOld) {
            $container->addPassword('old_password', _('Old password'))
                ->setHtmlAttribute('autocomplete', 'current-password')
                ->addRule(Form::FILLED)
                ->addCondition(Form::FILLED)
                ->addRule(
                    fn(BaseControl $control): bool => $this->model->calculateHash($control->getValue()) ===
                        $this->model->hash,
                    _('Incorrect old password.')
                );
        }
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();

        $loginData = FormUtils::emptyStrToNull2($values);
        /* if ($loginData['password']) {
             $loginData['hash'] = $login->calculateHash($loginData['password']);
         }*/

        $this->loginService->storeModel($loginData, $this->model);

        $this->getPresenter()->flashMessage(_('User information has been saved.'), Message::LVL_SUCCESS);
        /*   if ($tokenAuthentication) {
               $this->flashMessage(_('Password changed.'), Message::LVL_SUCCESS);
               $this->tokenAuthenticator->disposeAuthToken(); // from now on same like password authentication
           }*/
        $this->getPresenter()->redirect('this');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults($this->model->toArray());
    }
}
