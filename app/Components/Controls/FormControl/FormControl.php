<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;

/**
 * Bootstrap compatible form control with support for AJAX in terms
 * of form/container groups.
 */
class FormControl extends BaseComponent
{
    protected function createComponentForm(): Form
    {
        return new Form();
    }

    /**
     * @throws BadTypeException
     */
    final public function getForm(): Form
    {
        $component = $this->getComponent('form');
        if (!$component instanceof Form) {
            throw new BadTypeException(Form::class, $component);
        }
        return $component;
    }

    final public function render(): void
    {
        if (!isset($this->template->mainContainer)) {
            $this->template->form = $this->getComponent('form');
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.containers.latte');
    }

    public static function buildContainerAttributes(BaseControl $control, ?string $class = null): array
    {
        return [
            'class' => ($class ?? 'form-group') . ' mb-3'
                . ($control->hasErrors() ? ' has-error ' : ' ')
                . ($control->isRequired() ? 'required' : ''),
            'id' => $control->getHtmlId() . '-pair',
        ];
    }
}
