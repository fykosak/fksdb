<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRowException;
use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonNameTrait;
use Nette\Forms\Controls\BaseControl;

/**
 * Class PersonNameRowFactory
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class PersonNameRowFactory extends AbstractOrgRowFactory {
    use PersonNameTrait;

    /**
     * @return BaseControl
     * @throws AbstractRowException
     */
    public function createField(): BaseControl {
        throw new AbstractRowException();
    }
}
