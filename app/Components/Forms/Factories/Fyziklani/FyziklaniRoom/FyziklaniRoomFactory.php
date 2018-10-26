<?php

namespace FKSDB\Components\Forms\Factories\Fyziklani\FyziklaniRoom;

use http\Exception\InvalidArgumentException;
use Nette\Forms\Controls\BaseControl;

class FyziklaniRoomFactory {

    /**
     * @param $fieldName
     * @return BaseControl
     */
    public function createField($fieldName): BaseControl {
        switch ($fieldName) {
            case 'columns':
                return new ColumnsField();
            case 'name':
                return new NameField();
            case 'rows':
                return new RowsField();
            default:
                throw new InvalidArgumentException('Field ' . $fieldName . ' not exists');
        }
    }
}
