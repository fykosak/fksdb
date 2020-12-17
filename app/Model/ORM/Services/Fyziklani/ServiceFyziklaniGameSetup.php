<?php

namespace FKSDB\Model\ORM\Services\Fyziklani;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceFyziklaniGameSetup
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniGameSetup extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_FYZIKLANI_GAME_SETUP, ModelFyziklaniGameSetup::class);
    }
}
