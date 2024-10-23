<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\Utils\BaseComponent\BaseComponent;
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

    final public function injectPrimary(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @phpstan-return array<string,Test<PersonModel>>
     */
    public function getAllTests(): array
    {
        $tests = [];
        foreach (DataTestFactory::getPersonTests($this->container) as $test) {
            $tests[$test->getId()] = $test;
        }
        return $tests;
    }

    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addText('offset', _('Offset'))
            ->addRule(Form::INTEGER, _('Must be an integer'))
            ->setDefaultValue($this->offset);
        $form->addText('limit', _('Limit'))
            ->addRule(Form::INTEGER, _('Must be an integer'))
            ->setDefaultValue($this->limit);

        $testsContainer = new ContainerWithOptions($this->container);
        $testsContainer->setOption('label', _('Tests'));
        foreach (DataTestFactory::getPersonTests($this->container) as $index => $test) {
            $field = $testsContainer->addCheckbox((string)$index, $test->getTitle()->toHtml());
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
                    $this->tests[$testId] = $this->getAllTests()[$testId];
                }
            }
            $this->offset = $values['offset'];
            $this->limit = $values['limit'];
        };
        return $control;
    }

    /**
     * @phpstan-return  array<int,array{model:PersonModel,logs:array<string,TestMessage[]>}>
     */
    private function calculateProblems(): array
    {
        $query = $this->personService->getTable()->limit($this->limit, $this->offset);//@phpstan-ignore-line
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
            'tests' => $this->getAllTests(),
            'logs' => $this->calculateProblems(),
        ]);
    }
}
