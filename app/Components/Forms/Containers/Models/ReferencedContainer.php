<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\OmittedControlException;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container as DIContainer;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;

abstract class ReferencedContainer extends ContainerWithOptions
{

    public const ID_MASK = 'frm%s-%s';
    public const CSS_AJAX = 'ajax';
    public const CONTROL_COMPACT = '_c_compact';
    public const SUBMIT_CLEAR = '__clear';

    private ReferencedId $referencedId;

    protected bool $allowClear = true;

    private bool $attachedJS = false;

    public function __construct(DIContainer $container, bool $allowClear)
    {
        parent::__construct($container);
        $this->monitor(JavaScriptCollector::class, function () {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $this->updateHtmlData();
            }
        }, fn() => $this->attachedJS = false);
        $this->createClearButton();
        $this->createCompactValue();

        $this->setAllowClear($allowClear);
    }

    public function getReferencedId(): ReferencedId
    {
        return $this->referencedId;
    }

    public function setReferencedId(ReferencedId $referencedId): void
    {
        $this->referencedId = $referencedId;
    }

    public function setDisabled(bool $value = true): void
    {
        /** @var BaseControl $control */
        foreach ($this->getControls() as $control) {
            $control->setDisabled($value);
        }
    }

    protected function setAllowClear(bool $allowClear): void
    {
        $this->allowClear = $allowClear;
        /** @var SubmitButton $control */
        $control = $this->getComponent(self::SUBMIT_CLEAR);
        $control->setOption('visible', $allowClear);
    }

    protected function validateChildComponent(IComponent $child): void
    {
        if (!$child instanceof BaseControl && !$child instanceof ContainerWithOptions) {
            throw new InvalidStateException(
                __CLASS__ . ' can contain only components with get/set option funcionality, ' . get_class(
                    $child
                ) . ' given.'
            );
        }
    }

    public function setConflicts(iterable $conflicts, ?IContainer $container = null): void
    {
        $container = $container ?? $this;
        foreach ($conflicts as $key => $value) {
            $component = $container->getComponent($key, false);
            if ($component instanceof Container) {
                $this->setConflicts($value, $component);
            } elseif ($component instanceof BaseControl) {
                $component->addError(null);
            }
        }
    }

    private function createClearButton(): void
    {
        $submit = $this->addSubmit(self::SUBMIT_CLEAR, 'X')
            ->setValidationScope(null);
        $submit->getControlPrototype()->class[] = self::CSS_AJAX;
        $submit->onClick[] = function () {
            if ($this->allowClear) {
                $this->referencedId->setValue(null);
                $this->referencedId->invalidateFormGroup();
            }
        };
    }

    private function createCompactValue(): void
    {
        $this->addHidden(self::CONTROL_COMPACT);
    }

    /**
     * @note Must be called after a form is attached.
     */
    private function updateHtmlData(): void
    {
        $this->setOption(
            'id',
            sprintf(self::ID_MASK, $this->getForm()->getName(), $this->lookupPath('Nette\Forms\Form'))
        );
        $referencedId = $this->referencedId->getHtmlId();
        $this->setOption('data-referenced-id', $referencedId);
        $this->setOption('data-referenced', 1);
    }

    /**
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    abstract protected function configure(): void;

    abstract public function setModel(?ActiveRow $model, string $mode): void;
}
