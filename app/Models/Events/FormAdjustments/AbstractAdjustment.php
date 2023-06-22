<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Control;
use Nette\Forms\Control as FormControl;
use Nette\Forms\Form;
use Nette\SmartObject;

abstract class AbstractAdjustment implements FormAdjustment
{
    use SmartObject;

    public const DELIMITER = '.';

    private array $pathCache;

    final public function adjust(Form $form, ModelHolder $holder): void
    {
        $this->pathCache = [];
        /** @var Control $control */
        foreach ($form->getComponents(true, Control::class) as $control) {
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_container', '', $path);
            $path = str_replace(IComponent::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->pathCache[$path] = $control;
        }
        $this->innerAdjust($form, $holder);
    }

    abstract protected function innerAdjust(Form $form, ModelHolder $holder): void;

    final protected function getControl(string $mask): ?Control
    {
        return $this->pathCache[$mask] ?? null;
    }
}
