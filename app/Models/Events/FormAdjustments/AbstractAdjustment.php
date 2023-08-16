<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Components\Controls\Person\Detail\Component;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Control;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * @template THolder of ModelHolder
 * @phpstan-implements FormAdjustment<THolder>
 */
abstract class AbstractAdjustment implements FormAdjustment
{
    use SmartObject;

    public const DELIMITER = '.';
    /** @phpstan-var array<string,Control&Component> */
    private array $pathCache;

    final public function adjust(Form $form, ModelHolder $holder): void
    {
        $this->pathCache = [];
        /** @var Control&Component $control */
        foreach ($form->getComponents(true, Control::class) as $control) {
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_container', '', $path);
            $path = str_replace(IComponent::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->pathCache[$path] = $control;
        }
        $this->innerAdjust($form, $holder);
    }

    /**
     * @phpstan-param THolder $holder
     */
    abstract protected function innerAdjust(Form $form, ModelHolder $holder): void;

    final protected function getControl(string $mask): ?Control
    {
        return $this->pathCache[$mask] ?? null;
    }
}
