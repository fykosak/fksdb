<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
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

    public function __construct(Container $container, LoginModel $model)
    {
        parent::__construct($container, $model);
    }

    public function inject(LoginService $loginService): void
    {
        $this->loginService = $loginService;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);
        $login = $container->addText('login', _('Username'));
        $login->setHtmlAttribute('autocomplete', 'username');
        $login->addRule(
            fn(BaseControl $baseControl): bool => (new UniqueLogin($this->getContext(), $this->model))($baseControl)
                && (new UniqueEmail($this->getContext(), $this->model->person))($baseControl),
            _('This username is already taken.')
        );
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $loginData = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        $this->loginService->storeModel($loginData, $this->model);
        $this->getPresenter()->flashMessage(_('User information has been saved.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
    }
}
