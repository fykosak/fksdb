<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ParameterType;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\StoredQuery\ResultsGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exports\ExportFormatFactory;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterModel;
use FKSDB\Models\StoredQuery\StoredQuery;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

class ResultsComponent extends BaseComponent
{
    public const CONT_PARAMS = 'params';
    public const PARAMETER_URL_PREFIX = 'p_';
    /**
     * @persistent
     */
    public ?array $parameters;
    public ?StoredQuery $storedQuery = null;
    private ExportFormatFactory $exportFormatFactory;
    public bool $showParametrizeForm = true;

    final public function injectPrimary(
        ExportFormatFactory $exportFormatFactory
    ): void {
        $this->exportFormatFactory = $exportFormatFactory;
    }

    protected function createComponentGrid(): ResultsGrid
    {
        return new ResultsGrid($this->storedQuery, $this->getContext());
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentParametrizeForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $parameters = $this->createParametersValues($this->storedQuery->getQueryPattern()->getParameters());
        $form->addComponent($parameters, self::CONT_PARAMS);

        $form->addSubmit('execute', _('Execute'));
        $form->onSuccess[] = function (Form $form) {
            $this->parameters = [];
            $values = $form->getValues();
            foreach ($values[self::CONT_PARAMS] as $key => $values) {
                $this->parameters[$key] = $values['value'];
            }
        };
        return $control;
    }

    public function getSqlError(): ?\Throwable
    {
        static $error;
        if (!isset($error)) {
            try {
                if (isset($this->storedQuery)) {
                    $this->storedQuery->getColumnNames(); // this may throw \PDOException in the main query
                }
            } catch (\PDOException $exception) {
                $error = $exception;
            }
        }
        return $error;
    }

    /**
     * @throws BadTypeException
     */
    final public function render(): void
    {
        if (isset($this->parameters)) {
            $this->storedQuery->setParameters($this->parameters);
            $defaults = [];
            foreach ($this->parameters as $key => $value) {
                $defaults[$key] = ['value' => $value];
            }
            /** @var FormControl $formControl */
            $formControl = $this->getComponent('parametrizeForm');
            $formControl->getForm()->setDefaults([self::CONT_PARAMS => $defaults]);
        }
        $this->template->error = $this->getSqlError();
        $this->template->hasParameters = $this->showParametrizeForm && count($this->storedQuery->getQueryParameters());
        $this->template->showParametrizeForm = $this->showParametrizeForm;
        $this->template->hasStoredQuery = isset($this->storedQuery);
        $this->template->storedQuery = $this->storedQuery ?? null;
        $this->template->formats = $this->storedQuery ? $this->exportFormatFactory->defaultFormats : [];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.results.latte');
    }

    /**
     * @throws NotFoundException
     */
    public function handleFormat(string $format): void
    {
        if (isset($this->parameters)) {
            $this->storedQuery->setParameters($this->parameters);
        }
        try {
            $response = $this->exportFormatFactory->createFormat($format, $this->storedQuery)->getResponse();
            $this->presenter->sendResponse($response);
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundException(sprintf('Undefined format \'%s\'.', $format), $exception);
        }
    }

    /**
     * @param ParameterModel[] $queryParameters
     * TODO
     */
    private function createParametersValues(array $queryParameters, ?ControlGroup $group = null): ModelContainer
    {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        foreach ($queryParameters as $parameter) {
            $name = $parameter->name;
            $subContainer = new ModelContainer();
            $container->addComponent($subContainer, $name);
            // $subcontainer = $container->addContainer($name);

            switch ($parameter->type->value) {
                case ParameterType::INT:
                case ParameterType::STRING:
                    $valueElement = $subContainer->addText('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    if ($parameter->type->value == ParameterType::INT) {
                        $valueElement->addRule(Form::INTEGER, _('Parameter %label is numeric.'));
                    }

                    $valueElement->setDefaultValue($parameter->getDefaultValue());
                    break;
                case ParameterType::BOOL:
                    $valueElement = $subContainer->addCheckbox('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    $valueElement->setDefaultValue((bool)$parameter->getDefaultValue());
                    break;
            }
        }
        return $container;
    }
}
