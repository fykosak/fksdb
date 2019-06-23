<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKSDB\ORM\DbNames;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory extends SingleReflectionFactory {
    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON;
    }

    /**
     * @param $ajax
     * @param $label
     * @param IDataProvider $dataProvider
     * @param null $renderMethod
     * @return AutocompleteSelectBox
     */
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
}

