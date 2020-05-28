<?php

namespace FKSDB\Components\DatabaseReflection;
/**
 * Class BaseRow
 * *
 */
abstract class BaseRow extends AbstractRow {
    use DefaultPrinterTrait;

    /** @var string */
    private $title;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }

    public function getTitle(): string {
        return _($this->title);
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
