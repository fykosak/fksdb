<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Application\BadRequestException;
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
