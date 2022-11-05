<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Authentication\Exceptions\ChangeInProgressException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

class ChangeEmailComponent extends BaseComponent
{
    private SingleReflectionFormFactory $reflectionFormFactory;
    private AccountManager $accountManager;

    public function __construct(
        Container $container,
        private readonly PersonModel $person,
        private readonly string $lang
    ) {
        parent::__construct($container);
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
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.email.latte');
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $newEmailControl = $this->reflectionFormFactory->createField('person_info', 'email');
        $uniqueEmail = new UniqueEmail($this->container);
        $newEmailControl->addRule(
            fn(BaseControl $baseControl) => ($uniqueEmail)($baseControl),
            _('This email is already assigned to account')
        );
        $form->addComponent($newEmailControl, 'new_email');
        $form->addSubmit('submit', _('Change email'));
        $form->onSuccess[] = fn(Form $form) => $this->handleFormSuccess($form);
        return $control;
    }

    /**
     * @throws BadTypeException
     * @throws ChangeInProgressException
     */
    private function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $this->accountManager->sendChangeEmail($this->person, $values['new_email'], Language::from($this->lang));
        $this->getPresenter()->flashMessage(
            _('Email with verification link was send to new email address, link is active for 20 minutes.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }
}
