<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Form;

abstract class FilterListComponent extends ListComponent
{
    /** @persistent */
    public array $filterParams = [];

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'filter.latte';
    }

    public function render(): void
    {
        $this->template->filterParams = $this->filterParams;
        parent::render();
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentFilterForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $this->configureForm($form);
        $applyButton = $control->getForm()->addSubmit('apply', _('Apply filter'));
        $resetButton = $control->getForm()->addSubmit('reset', _('Reset filter'));
        $applyButton->onClick[] = function () use ($form): void {
            $this->filterParams = FormUtils::emptyStrToNull2($form->getValues('array'));
            $this->redirect('this');
        };
        $resetButton->onClick[] = function (): void {
            $this->filterParams = [];
            $this->redirect('this');
        };
        $form->setDefaults($this->filterParams);
        return $control;
    }

    public function handleDelete(string $param): void
    {
        $this->filterParams[$param] = null;
        $this->redirect('this');
    }

    abstract protected function configureForm(Form $form): void;
}
