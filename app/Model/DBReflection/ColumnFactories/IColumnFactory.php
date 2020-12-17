<?php

namespace FKSDB\Model\DBReflection\ColumnFactories;

use FKSDB\Model\DBReflection\FieldLevelPermission;
use FKSDB\Model\DBReflection\OmittedControlException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

interface IColumnFactory {
    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     * @throws AbstractColumnException
     */
    public function createField(...$args): BaseControl;

    public function getDescription(): ?string;

    public function render(AbstractModelSingle $model, int $userPermissionsLevel): Html;

    public function getTitle(): string;

    public function getPermission(): FieldLevelPermission;

    public function hasReadPermissions(int $userValue): bool;

    public function hasWritePermissions(int $userValue): bool;
}
