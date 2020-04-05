<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationFactory;
use Nette\DI\Container;

/**
 * Class StalkingValidation
 * @package FKSDB\ValidationTest
 */
class Validation extends AbstractStalkingComponent {
    /**
     * @var ValidationFactory
     */
    private $validationFactory;

    /**
     * Validation constructor.
     * @param Container $container
     * @param ModelPerson $modelPerson
     * @param $mode
     */
    public function __construct(Container $container, ModelPerson $modelPerson, $mode) {
        parent::__construct($container, $modelPerson, $mode);
        $this->validationFactory = $container->getByType(ValidationFactory::class);
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
