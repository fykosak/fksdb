<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueLogin;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<LoginModel,array{login:array{login:string}}>
 */
class LoginFomComponent extends ModelForm
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

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
    }

    protected function innerSuccess(array $values, Form $form): Model
    {
        /** @var LoginModel $login */
        $login = $this->loginService->storeModel($values[self::CONTAINER], $this->model);
        return $login;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(_('User information has been saved.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}
