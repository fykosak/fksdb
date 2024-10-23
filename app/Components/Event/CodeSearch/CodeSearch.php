<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeSearch;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\MachineCode\MachineCode;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 */
final class CodeSearch extends CodeForm
{
    /** @phpstan-var callable(TSupportedModel):void */
    private $callback;
    private string $salt;

    /** @phpstan-param callable(TSupportedModel):void $callback */
    public function __construct(Container $container, callable $callback, string $salt)
    {
        parent::__construct($container);
        $this->callback = $callback;
        $this->salt = $salt;
    }

    protected function innerHandleSuccess(Model $model, Form $form): void
    {
        ($this->callback)($model);
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);
        $form->getComponent('code')->setHtmlAttribute('autofocus', '1');
    }

    protected function getSalt(): string
    {
        return $this->salt;
    }
}
