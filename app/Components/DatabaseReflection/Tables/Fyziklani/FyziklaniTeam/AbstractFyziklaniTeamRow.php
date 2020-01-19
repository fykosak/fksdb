<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\IFyziklaniTeamReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Localization\ITranslator;

/**
 * Class AbstractFyziklaniRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
abstract class AbstractFyziklaniTeamRow extends AbstractRow {

    /**
     * NameRow constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        parent::__construct($translator);
        $this->setReferencedParams(ModelFyziklaniTeam::class, [
            'modelClassName' => IFyziklaniTeamReferencedModel::class,
            'method' => 'getFyziklaniTeam',
            'nullable' => false,
        ]);
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(): BaseControl {
        throw new BadRequestException();
    }
}
