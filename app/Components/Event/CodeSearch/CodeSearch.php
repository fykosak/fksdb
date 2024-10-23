<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeSearch;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\DI\Container;
use Nette\Forms\Form;

final class CodeSearch extends CodeForm
{
    /** @phpstan-var callable(PersonModel|TeamModel2):void */
    private $callback;
    private string $salt;

    /** @phpstan-param callable(PersonModel|TeamModel2):void $callback */
    public function __construct(Container $container, callable $callback, string $salt)
    {
        parent::__construct($container);
        $this->callback = $callback;
        $this->salt = $salt;
    }

    protected function innerHandleSuccess(PersonModel|TeamModel2 $model, Form $form): void
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
