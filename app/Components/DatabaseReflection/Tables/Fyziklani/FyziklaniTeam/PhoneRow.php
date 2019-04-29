<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\PhoneRowTrait;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;

/**
 * Class PhoneRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class PhoneRow extends AbstractFyziklaniTeamRow implements ITestedRowFactory {
    use PhoneRowTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone');
    }

    /**
     * @return string
     */
    public function getModelAccessKey(): string {
        return 'phone';
    }
}
