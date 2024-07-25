<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Login;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class LoginForm extends FormComponent
{
    private User $user;
    /** @var callable():void */
    private $initialRedirect;

    /**
     * @param callable():void $initialRedirect
     */
    public function __construct(Container $container, callable $initialRedirect)
    {
        parent::__construct($container);
        $this->initialRedirect = $initialRedirect;
    }

    public function inject(User $user): void
    {
        $this->user = $user;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . '/layout.latte';
    }

    protected function handleSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{id:string,password:string} $values
         */
        $values = $form->getValues('array');
        try {
            $this->user->login($values['id'], $values['password']);
            ($this->initialRedirect)();
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('send', _('Log in'));
    }

    protected function configureForm(Form $form): void
    {
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

        $form->addProtection(_('The form has expired. Please send it again.'));
    }
}
