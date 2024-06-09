<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-template TModel of Model
 */
class TestsList extends BaseComponent
{
    /** @phpstan-var Test<TModel>[] */
    private array $availableTests;
    /**
     * @var string[]
     * @persistent
     * */
    public array $selectedTests = [];

    /**
     * @phpstan-param Test<TModel>[] $tests
     */
    public function __construct(Container $container, array $tests)
    {
        parent::__construct($container);
        $this->availableTests = $tests;
    }

    public function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        foreach ($this->availableTests as $test) {
            $form->addCheckbox($test->getTreeId(), $test->getTitle()->title);
        }
        $form->onSuccess[] = function (Form $form): void {
            $this->selectedTests = $form->getValues('array');
            $this->redirect('this');
        };
        $form->addSubmit('submit', _('Submit'));
        $form->setDefaults($this->selectedTests);
        return $control;
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, bool $list = true): void
    {
        $data = [];
        foreach ($this->availableTests as $test) {
            if (isset($this->selectedTests[$test->getTreeId()]) && $this->selectedTests[$test->getTreeId()]) {
                $logger = new TestLogger();
                $test->run($logger, $model);
                if (count($logger->getMessages())) {
                    $data[] = [
                        'messages' => $logger->getMessages(),
                        'test' => $test,
                    ];
                }
            }
        }
        if ($list) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'list.latte', ['data' => $data]);
        } else {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'noList.latte', ['data' => $data]);
        }
    }
}
