<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class PreferredLangFormComponent extends FormComponent
{
    protected PersonModel $person;
    protected PersonInfoService $personInfoService;

    public function __construct(Container $container, PersonModel $person)
    {
        parent::__construct($container);
        $this->person = $person;
    }

    final public function injectServicePersonInfo(PersonInfoService $personInfoService): void
    {
        $this->personInfoService = $personInfoService;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('button.save'));
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{preferred_lang:string} $values */
        $values = $form->getValues('array');
        try {
            $this->personInfoService->storeModel(
                [
                    'preferred_lang' => $values['preferred_lang'],
                    'person_id' => $this->person->person_id,
                ],
                $this->person->getInfo()
            );
            $this->flashMessage(_('Preferred language has been set.'), Message::LVL_SUCCESS);
            $this->getPresenter()->redirect('this');
        } catch (\PDOException $exception) {
            $this->flashMessage(_('Error'), Message::LVL_ERROR);
        }
    }

    /**
     * @throws NotImplementedException
     */
    protected function configureForm(Form $form): void
    {
        $items = [];
        foreach (Language::cases() as $lang) {
            $items[$lang->value] = $lang->label();
        }
        $form->addSelect('preferred_lang')->setItems($items);
    }

    final public function render(): void
    {
        $this->getForm()->setDefaults(['preferred_lang' => $this->person->getPreferredLang()]);
        parent::render();
    }
}
