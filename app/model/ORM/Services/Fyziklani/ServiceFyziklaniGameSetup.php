<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
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
