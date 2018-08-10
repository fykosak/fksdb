<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKSDB\Components\Forms\Factories\Person\DisplayNameField;
use FKSDB\Components\Forms\Factories\Person\FamilyNameField;
use FKSDB\Components\Forms\Factories\Person\GenderField;
use FKSDB\Components\Forms\Factories\Person\OtherNameField;
use Nette\InvalidArgumentException;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 *
 */
class PersonFactory {
// TODO vykuchať!!!!
    public function createPersonSelect($ajax, $label, IDataProvider $dataProvider, $renderMethod = null) {
        if ($renderMethod === null) {
            $renderMethod = '$("<li>")
                        .append("<a>" + item.label + "<br>" + item.place + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        }
        $select = new AutocompleteSelectBox($ajax, $label, $renderMethod);
        $select->setDataProvider($dataProvider);
        return $select;
    }

    /**
     * @param $fieldName
     * @return DisplayNameField|FamilyNameField|GenderField|OtherNameField
     */
    public function createField($fieldName) {
        switch ($fieldName) {
            case 'other_name':
                return new OtherNameField();
            case 'family_name':
                return new FamilyNameField();
            case 'display_name':
                return new DisplayNameField();
            case 'gender':
                return new GenderField();
            default:
                throw new InvalidArgumentException();
        }
    }

    public function createReactField($fieldName) {
        return $this->createField($fieldName)->getReactDefinition();
    }
}
