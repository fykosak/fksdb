<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

interface IColumnFactory {
    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    public function createField(...$args): BaseControl;

    /**
     * @return string|null
     */
    public function getDescription();

    public function renderValue(AbstractModelSingle $model, int $userPermissionsLevel): Html;

    public function getTitle(): string;

    public function getPermission(): FieldLevelPermission;

    public function hasReadPermissions(int $userValue): bool;

    public function hasWritePermissions(int $userValue): bool;
}
