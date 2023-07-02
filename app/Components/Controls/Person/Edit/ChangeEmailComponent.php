<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Authentication\Exceptions\ChangeInProgressException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @property-read PersonModel $model
 */
class ChangeEmailComponent extends EntityFormComponent
{
    private SingleReflectionFormFactory $reflectionFormFactory;
    private AccountManager $accountManager;
    private string $lang;

    public function __construct(
        Container $container,
        PersonModel $person,
        string $lang
    ) {
        parent::__construct($container, $person);
        $this->lang = $lang;
    }

    public function inject(
        AccountManager $accountManager,
        SingleReflectionFormFactory $reflectionFormFactory
    ): void {
        $this->accountManager = $accountManager;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    public function render(): void
    {
        $login = $this->model->getLogin();
        $this->template->lang = Language::tryFrom($this->lang);
        $this->template->changeActive = $login &&
            $login->getActiveTokens(AuthTokenType::tryFrom(AuthTokenType::CHANGE_EMAIL))->fetch();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.email.latte';
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $newEmailControl = $this->reflectionFormFactory->createField('person_info', 'email');
        $uniqueEmail = new UniqueEmail($this->container);
        $newEmailControl->addRule(
            fn(BaseControl $baseControl) => ($uniqueEmail)($baseControl),
            _('This email is already in use.')
        );
        $form->addComponent($newEmailControl, 'new_email');
    }

    protected function appendSubmitButton(Form $form): void
    {
        $form->addSubmit('submit', _('Change email'));
    }

    /**
     * @throws BadTypeException
     * @throws ChangeInProgressException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $this->accountManager->sendChangeEmail($this->model, $values['new_email'], Language::tryFrom($this->lang));
        $this->getPresenter()->flashMessage(
            _('Email with a verification link has been sent to the new email address,' .
                ' the link is active for 20 minutes.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults(['new_email' => $this->model->getInfo() ? $this->model->getInfo()->email : null]);
    }
}
