<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKSDB\Components\Forms\Factories\Person\DisplayNameField;
use FKSDB\Components\Forms\Factories\Person\FamilyNameField;
use FKSDB\Components\Forms\Factories\Person\GenderField;
use FKSDB\Components\Forms\Factories\Person\OtherNameField;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory {
    // For person

    const SHOW_DISPLAY_NAME = 0x1;
    const SHOW_GENDER = 0x2;
    const DISABLED = 0x4;

    // For person_info
    /** @const Show iformation important for organizers. */
    const SHOW_ORG_INFO = 0x8;
    const SHOW_EMAIL = 0x10;
    const REQUIRE_AGREEMENT = 0x20;
    const SHOW_LOGIN_CREATION = 0x40;
    /** @const Display origin and agreement only (supplement to other form containers). */
    const SHOW_LIKE_SUPPLEMENT = 0x100;
    const REQUIRE_EMAIL = 0x200;

    // For person_history
    const REQUIRE_SCHOOL = 0x400;
    const REQUIRE_STUDY_YEAR = 0x800;
    /** @const Display school, study year and class only (supplement to other form containers). */
    const SHOW_LIKE_CONTESTANT = 0x1000;

    /* Encapsulation condition argument (workaround) */
    const IDX_CONTROL = 'control';
    const IDX_OPERATION = 'op';
    const IDX_VALUE = 'val';

    /* Subcontainers names */
    const CONT_LOGIN = 'logincr';

    /* Element names */
    const EL_CREATE_LOGIN = 'createLogin';
    const EL_CREATE_LOGIN_LANG = 'lang';

    public function createPersonSelect($ajax, $label, IDataProvider $dataProvider, $renderMethod = null): AutocompleteSelectBox {
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
     * @return BaseControl
     */
    public function createField($fieldName): BaseControl {
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
}

