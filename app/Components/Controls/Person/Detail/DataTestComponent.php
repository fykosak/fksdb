<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;

class DataTestComponent extends BaseComponent
{
    private DataTestFactory $factory;

    final public function injectDataTestFactory(DataTestFactory $factory): void
    {
        $this->factory = $factory;
    }

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->render(
                __DIR__ . DIRECTORY_SEPARATOR . 'dataTest.latte',
                [
                    'person' => $this->person,
                ]
            );
        }
    }

    /**
     * @phpstan-return TestsList<PersonModel>
     */
    protected function createComponentTests(): TestsList
    {
        return new TestsList($this->container, $this->factory->getPersonTests());
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_FULL;
    }
}
