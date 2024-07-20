<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Models\ORM\Models\PersonEmailPreferenceModel;
use FKSDB\Models\ORM\Models\PersonEmailPreferenceOption;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonEmailPreferenceService;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<PersonModel>
 */
class EmailPreferenceForm extends EntityFormComponent
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

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        foreach (PersonEmailPreferenceOption::cases() as $case) {
            $preference = $this->model->getEmailPreferences()->where('option', $case->value)->fetch();
            $value = $values[$case->value];
            $this->emailPreferenceService->storeModel([
                'person_id' => $this->model->person_id,
                'option' => $case->value,
                'value' => $value,
            ], $preference);
        }
    }

    protected function setDefaults(Form $form): void
    {
        $defaults = [];
        foreach (PersonEmailPreferenceOption::cases() as $case) {
            /** @var PersonEmailPreferenceModel $preference */
            $preference = $this->model->getEmailPreferences()->where('option', $case->value)->fetch();
            $defaults[$case->value] = $preference ? $preference->option : true;
        }
        $form->setDefaults($defaults);
    }
}
