<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Models\ORM\Models\PersonEmailPreferenceModel;
use FKSDB\Models\ORM\Models\PersonEmailPreferenceOption;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonEmailPreferenceService;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<PersonModel,array<string,bool>>
 */
class EmailPreferenceForm extends ModelForm
{
    private PersonEmailPreferenceService $emailPreferenceService;

    public function inject(PersonEmailPreferenceService $emailPreferenceService): void
    {
        $this->emailPreferenceService = $emailPreferenceService;
    }

    protected function configureForm(Form $form): void
    {
        foreach (PersonEmailPreferenceOption::cases() as $case) {
            $form->addCheckbox($case->value, $case->label());
        }
    }

    protected function setDefaults(Form $form): void
    {
        $defaults = [];
        foreach (PersonEmailPreferenceOption::cases() as $case) {
            /** @var PersonEmailPreferenceModel|null $preference */
            $preference = $this->model->getEmailPreferences()->where('option', $case->value)->fetch();
            $defaults[$case->value] = $preference ? $preference->option : true;
        }
        $form->setDefaults($defaults);
    }

    protected function innerSuccess(array $values, Form $form): Model
    {
        foreach (PersonEmailPreferenceOption::cases() as $case) {
            /** @var PersonEmailPreferenceModel|null $preference */
            $preference = $this->model->getEmailPreferences()->where('option', $case->value)->fetch();
            $value = $values[$case->value];
            $this->emailPreferenceService->storeModel([
                'person_id' => $this->model->person_id,
                'option' => $case->value,
                'value' => $value,
            ], $preference);
        }
        return $this->model;
    }

    protected function successRedirect(Model $model): void
    {
// TODO
    }
}
