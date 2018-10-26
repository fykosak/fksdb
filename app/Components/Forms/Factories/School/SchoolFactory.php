<?php

namespace FKSDB\Components\Forms\Factories\School;

use http\Exception\InvalidArgumentException;
use Nette\Forms\Controls\BaseControl;

class SchoolFactory {
    public function createField($fieldName):BaseControl {

        switch ($fieldName) {
            case'name_full':
                return new FullNameField();
            case'name':
                return new NameField();
            case'name_abbrev':
                return new NameAbbrevField();
            case'email':
                return new EmailField();
            case'ic':
                return new ICField();
            case'izo':
                return new IZOField();
            case'active':
                return new ActiveField();
            case 'note':
                return new NoteField();
            default:
                throw new InvalidArgumentException('Field ' . $fieldName . ' not exists');

        }
    }
}
