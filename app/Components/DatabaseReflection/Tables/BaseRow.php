<?php

namespace FKSDB\Components\DatabaseReflection;
/**
 * Class BaseRow
 * @package FKSDB\Components\DatabaseReflection
 */
class BaseRow extends AbstractRow {
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
        return $this->title;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
