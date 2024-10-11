<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Edit;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\FlagModel;
use FKSDB\Models\ORM\Models\PersonHasFlagModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonHasFlagService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property PersonHasFlagModel $model
 * @phpstan-extends ModelForm<PersonHasFlagModel,array{value:string}>
 */
class FlagComponent extends ModelForm
{
    private FlagModel $flag;
    private PersonModel $person;
    private PersonHasFlagService $personHasFlagService;

    /**
     * @throws NotImplementedException
     */
    public function __construct(Container $container, ?PersonHasFlagModel $model, FlagModel $flag, PersonModel $person)
    {
        parent::__construct($container, $model);
        $this->flag = $flag;
        $this->person = $person;
        if ($flag->type !== 'global') {
            throw new NotImplementedException();
        }
    }

    public function inject(PersonHasFlagService $personHasFlagService): void
    {
        $this->personHasFlagService = $personHasFlagService;
    }

    protected function configureForm(Form $form): void
    {
        $form->addSelect('flag_value', _('Value'), [
            'yes' => _('Yes'),
            'no' => _('No'),
        ])->setPrompt(_('No selected'));
    }

    protected function setDefaults(Form $form): void
    {
        if (!isset($this->model)) {
            $value = null;
        } else {
            $value = $this->model->value ? 'yes' : 'no';
        }
        $this->getForm()->setDefaults(['flag_value' => $value]);
    }

    protected function innerSuccess(array $values, Form $form): Model
    {
        $flagValue = null;
        if ($values['value'] === 'yes') {
            $flagValue = 1;
        } elseif ($values['value'] === 'no') {
            $flagValue = 0;
        }
        if (isset($flagValue)) {
            $this->personHasFlagService->storeModel([
                'person_id' => $this->person->person_id,
                'flag_id' => $this->flag->flag_id,
                'value' => $flagValue,
            ], $this->model);
        } elseif (isset($this->model)) {
            $this->personHasFlagService->disposeModel($this->model);
        }
        return $this->model;
    }

    protected function successRedirect(Model $model): void
    {
        $this->flashMessage(_('Flag setting has been saved'), Message::LVL_SUCCESS);
    }
}
