<?php

namespace FKSDB\Components\DatabaseReflection;
/**
 * Class StringRow
 * @package FKSDB\Components\DatabaseReflection
 */
class StringRow extends AbstractRow {
    use DefaultPrinterTrait;
    /**
     * @var string
     */
    private $title;
    /**
     * @var int
     */
    private $permissionsValue;
    /**
     * @var string
     */
    private $modelAccessKey;

    /**
     * @param string $title
     * @param int $permissionsValue
     * @param string $modelAccessKey
     */
    public function setUp(string $title, int $permissionsValue, string $modelAccessKey) {
        $this->title = $title;
        $this->permissionsValue = $permissionsValue;
        $this->modelAccessKey = $modelAccessKey;
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return $this->permissionsValue;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        $this->title;
    }

    /**
     * @inheritDoc
     */
    protected function getModelAccessKey(): string {
        return $this->modelAccessKey;
    }
}
