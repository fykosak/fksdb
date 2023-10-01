<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;

class PersonTestComponent extends BaseComponent
{
    /**
     * @persistent
     */
    public ?int $offset = 0;
    /**
     * @persistent
     */
    public ?int $limit = 0;
    /**
     * @phpstan-var array<string,Test<PersonModel>>|null
     * @persistent
     */
    public ?array $tests = [];

    private PersonService $personService;
    private DataTestFactory $dataTestFactory;

    final public function injectPrimary(PersonService $personService, DataTestFactory $dataTestFactory): void
    {
        $this->personService = $personService;
        $this->dataTestFactory = $dataTestFactory;
    }

    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('offset', _('Offset'))
            ->addRule(Form::INTEGER, _('Must be a int'))
            ->setDefaultValue($this->offset);
        $form->addText('limit', _('Limit'))
            ->addRule(Form::INTEGER, _('Must be a int'))
            ->setDefaultValue($this->limit);

        $testsContainer = new ContainerWithOptions($this->container);
        $testsContainer->setOption('label', _('Tests'));
        foreach ($this->dataTestFactory->getTests('person') as $key => $test) {
            $field = $testsContainer->addCheckbox($key, $test->getTitle()->title);
            if (\in_array($test, $this->tests)) {
                $field->setDefaultValue(true);
            }
        }
        $form->addComponent($testsContainer, 'tests');

        $form->addSubmit('submit');
        $form->onSuccess[] = function (Form $form) {
            /** @phpstan-var array{
             *     tests:array<string,bool>,
             *     offset:int,
             *     limit:int
             * } $values
             */
            $values = $form->getValues('array');

            $this->tests = [];
            foreach ($values['tests'] as $testId => $value) {
                if ($value) {
                    $this->tests[$testId] = $this->dataTestFactory->getTests('person')[$testId];
                }
            }
            $this->offset = $values['offset'];
            $this->limit = $values['limit'];
        };
        return $control;
    }

    /**
     * @phpstan-return  array<int,array{model:PersonModel,logs:array<string,Message[]>}>
     */
    private function calculateProblems(): array
    {
        $query = $this->personService->getTable()->limit($this->limit, $this->offset);
        $logs = [];
        /** @var PersonModel $model */
        foreach ($query as $model) {
            $personLog = DataTestFactory::runForModel($model, $this->tests);
            if (\count($personLog)) {
                $logs[] = ['model' => $model, 'logs' => $personLog];
            }
        }
        return $logs;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', [
            'tests' => $this->dataTestFactory->getTests('person'),
            'logs' => $this->calculateProblems(),
        ]);
    }
}