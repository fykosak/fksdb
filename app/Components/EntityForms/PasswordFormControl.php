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
class PasswordFormControl extends EntityFormComponent
{
    private LoginService $loginService;
    private const CONTAINER = 'login';

    public function __construct(Container $container, LoginModel $model, private readonly bool $verifyOld = true)
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
        $container->addPassword('password', _('Password'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->addCondition(Form::FILLED)
            ->addRule(
                Form::MIN_LENGTH,
                _('The password must have at least %d characters.'),
                6
            );
        if ($this->verifyOld) {
            $container->addPassword('old_password', _('Old password'))
                ->setHtmlAttribute('autocomplete', 'current-password')
                ->addRule(Form::FILLED)
                ->addCondition(Form::FILLED)
                ->addRule(
                    fn(BaseControl $control): bool => $this->model->calculateHash($control->getValue())
                        === $this->model->hash,
                    _('Incorrect old password.')
                );
        }
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();

        $loginData = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        if ($loginData['password']) {
            $loginData['hash'] = $this->model->calculateHash($loginData['password']);
        }

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
