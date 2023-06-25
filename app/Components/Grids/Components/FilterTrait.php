<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

trait FilterTrait
{
    /** @persistent */
    public array $filterParams = [];

    public function traitRender(): void
    {
        $this->template->filterParams = $this->filterParams;
    }

    final protected function createComponentFilterForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $this->configureForm($form);
        $applyButton = $control->getForm()->addSubmit('apply', _('Apply filter!'));
        $resetButton = $control->getForm()->addSubmit('reset', _('Reset filter!'));
        $applyButton->onClick[] = function (SubmitButton $button): void {
            $this->filterParams = FormUtils::emptyStrToNull2($button->getForm()->getValues('array'));
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
        unset($this->filterParams[$param]);
        $this->redirect('this');
    }

    abstract protected function configureForm(Form $form): void;

    abstract protected function getContext(): Container;
}
