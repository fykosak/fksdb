<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Controls\ReferencedIdMode;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\OmittedControlException;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container as DIContainer;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\InvalidStateException;

/**
 * @template M of Model
 */
abstract class ReferencedContainer extends ContainerWithOptions
{
    public const ID_MASK = 'frm%s-%s';
    public const CONTROL_COMPACT = '_c_compact';
    public const SUBMIT_CLEAR = '__clear';
    /**
     * @phpstan-var ReferencedId<M>|null
     */
    private ?ReferencedId $referencedId = null;

    protected bool $allowClear = true;

    private bool $attachedJS = false;
    private bool $configured = false;

    public function __construct(DIContainer $container, bool $allowClear)
    {
        parent::__construct($container);
        $this->monitor(IPresenter::class, function () {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $this->updateHtmlData();
            }
        }, fn() => $this->attachedJS = false);
        $this->monitor(IContainer::class, function (): void {
            if (!$this->configured) {
                $this->configured = true;
                $this->configure();
            }
        });
        $this->createClearButton();
        $this->createCompactValue();

        $this->setAllowClear($allowClear);
    }

    /**
     * @phpstan-return ReferencedId<M>|null
     */
    public function getReferencedId(): ?ReferencedId
    {
        return $this->referencedId;
    }

    /**
     * @phpstan-param ReferencedId<M> $referencedId
     */
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
                self::class . ' can contain only components with get/set option funcionality, ' . get_class(
                    $child
                ) . ' given.'
            );
        }
    }

    /**
     * @phpstan-param array<string,scalar|null>|array<string,array<string,scalar|null>> $conflicts
     */
    public function setConflicts(array $conflicts, ?ContainerWithOptions $container = null): void
    {
        $container = $container ?? $this;
        foreach ($conflicts as $key => $value) {
            $component = $container->getComponent($key, false);
            if ($component instanceof ContainerWithOptions) {
                /** @phpstan-var array<string,scalar|null> $value */
                $this->setConflicts($value, $component);
            } elseif ($component instanceof BaseControl) {
                $component->addError(_('Field does not match an existing record.'));
            }
        }
    }

    private function createClearButton(): void
    {
        $submit = $this->addSubmit(self::SUBMIT_CLEAR, 'X')
            ->setValidationScope(null);
        $cb = function (): void {
            if ($this->allowClear) {
                $this->referencedId->setValue(null);
            }
        };
        $submit->onClick[] = $cb;
        $submit->onInvalidClick[] = $cb;
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
            sprintf(self::ID_MASK, $this->getForm()->getName(), $this->lookupPath(Form::class))
        );
        if (isset($this->referencedId)) {
            $referencedId = $this->referencedId->getHtmlId();
            $this->setOption('data-referenced-id', $referencedId);
            $this->setOption('data-referenced', 1);
        }
    }

    /**
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    abstract protected function configure(): void;

    abstract public function setModel(?Model $model, ReferencedIdMode $mode): void;
}
