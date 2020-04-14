<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\DataTestingFactory;
use Nette\DI\Container;

/**
 * Class StalkingValidation
 * @package FKSDB\ValidationTest
 */
class Validation extends AbstractStalkingComponent {
    /**
     * @var DataTestingFactory
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
        $this->validationFactory = $container->getByType(DataTestingFactory::class);
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
        $logger = new TestsLogger();
        foreach ($this->validationFactory->getTests('person') as $test) {
            $test->run($logger, $this->modelPerson);
        }

        $this->template->logs = $logger->getLogs();
        $this->template->setFile(__DIR__ . '/Validation.latte');
        $this->template->render();
    }
}
