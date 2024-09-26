<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Models\ORM\Models\PersonCorrespondencePreferenceModel;
use FKSDB\Models\ORM\Models\PersonCorrespondencePreferenceOption;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonCorrespondencePreferenceService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<PersonModel,array<string,bool>>
 */
class EmailPreferenceForm extends ModelForm
{
    private PersonCorrespondencePreferenceService $correspondencePreferenceService;

    public function inject(PersonCorrespondencePreferenceService $correspondencePreferenceService): void
    {
        $this->correspondencePreferenceService = $correspondencePreferenceService;
    }

    protected function configureForm(Form $form): void
    {
        foreach (PersonCorrespondencePreferenceOption::emailCases() as $case) {
            $input = $form->addCheckbox($case->value, $case->label());
            $input->setOption('description', $case->description());
        }
    }

    protected function setDefaults(Form $form): void
    {
        $defaults = [];
        foreach (PersonCorrespondencePreferenceOption::emailCases() as $case) {
            /** @var PersonCorrespondencePreferenceModel|null $preference */
            $preference = $this->model->getCorrespondencePreference()->where('option', $case->value)->fetch();
            $defaults[$case->value] = $preference ? $preference->value : true;
        }
        $form->setDefaults($defaults);
    }

    protected function innerSuccess(array $values, Form $form): Model
    {
        foreach (PersonCorrespondencePreferenceOption::emailCases() as $case) {
            /** @var PersonCorrespondencePreferenceModel|null $preference */
            $preference = $this->model->getCorrespondencePreference()->where('option', $case->value)->fetch();
            $value = $values[$case->value];
            $this->correspondencePreferenceService->storeModel([
                'person_id' => $this->model->person_id,
                'option' => $case->value,
                'value' => $value,
            ], $preference);
        }
        return $this->model;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(_('Preferences saved'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}
