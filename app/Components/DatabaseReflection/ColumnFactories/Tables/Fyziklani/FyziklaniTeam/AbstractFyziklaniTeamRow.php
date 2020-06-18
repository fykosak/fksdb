<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class AbstractFyziklaniTeamRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractFyziklaniTeamRow extends AbstractColumnFactory {

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }
}
