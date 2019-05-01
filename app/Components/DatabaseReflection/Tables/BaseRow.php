<?php

namespace FKSDB\Components\DatabaseReflection;
/**
 * Class BaseRow
 * @package FKSDB\Components\DatabaseReflection
 */
abstract class BaseRow extends AbstractRow {
    use DefaultPrinterTrait;
    private $title;

    /**
     * @param string $title
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _($this->title);
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
