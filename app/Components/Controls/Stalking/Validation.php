<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationFactory;
use Nette\Localization\ITranslator;

/**
 * Class StalkingValidation
 * @package FKSDB\ValidationTest
 */
class Validation extends StalkingComponent {
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    /**
     * Validation constructor.
     * @param ValidationFactory $validationFactory
     * @param TableReflectionFactory $factory
     * @param ModelPerson $modelPerson
     * @param ITranslator $translator
     * @param $mode
     */
    public function __construct(ValidationFactory $validationFactory, TableReflectionFactory $factory, ModelPerson $modelPerson, ITranslator $translator, $mode) {
        parent::__construct($modelPerson, $factory, $translator, $mode);
        $this->validationFactory = $validationFactory;
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Validation');
    }

    /**
     * @return array
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_RESTRICT, self::PERMISSION_FULL, self::PERMISSION_FULL];
    }

    public function render() {
        $this->beforeRender();
        $logs = [];
        foreach ($this->validationFactory->getTests() as $test) {
            $logs[] = $test->run($this->modelPerson);
        }

        $this->template->logs = $logs;
        $this->template->setFile(__DIR__ . '/Validation.latte');
        $this->template->render();
    }
}
