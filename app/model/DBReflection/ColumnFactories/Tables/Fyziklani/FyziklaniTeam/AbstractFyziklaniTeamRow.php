<?php

namespace FKSDB\DBReflection\ColumnFactories\Fyziklani\FyziklaniTeam;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\OmittedControlException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class AbstractFyziklaniTeamRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractFyziklaniTeamRow extends AbstractColumnFactory {

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function createField(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
