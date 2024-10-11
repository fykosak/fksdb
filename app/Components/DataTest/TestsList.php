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
    private bool $filtered;

    /**
     * @phpstan-param Test<TModel>[] $tests
     */
    public function __construct(Container $container, array $tests, bool $filtered)
    {
        parent::__construct($container);
        $this->availableTests = $tests;
        $this->filtered = $filtered;
    }

    public function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        foreach ($this->availableTests as $test) {
            $field = $form->addCheckbox($test->getTreeId(), $test->getTitle()->title);
            $field->setOption('description', $test->getDescription());
        }
        $form->onSuccess[] = function (Form $form): void {
            /** @phpstan-ignore-next-line */
            $this->selectedTests = array_keys(array_filter($form->getValues('array'), fn($value) => $value));
            $this->redirect('this');
        };
        $form->addSubmit('submit', _('Submit'));
        $form->setDefaults(array_fill_keys($this->selectedTests, true));
        return $control;
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, bool $list = true): void
    {
        $data = [];
        foreach ($this->availableTests as $test) {
            if ($this->filtered && in_array($test->getTreeId(), $this->selectedTests)) {
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
            $this->template->render(
                __DIR__ . DIRECTORY_SEPARATOR . 'list.latte',
                ['data' => $data, 'filtered' => $this->filtered]
            );
        } else {
            $this->template->render(
                __DIR__ . DIRECTORY_SEPARATOR . 'noList.latte',
                ['data' => $data, 'filtered' => $this->filtered]
            );
        }
    }
}
