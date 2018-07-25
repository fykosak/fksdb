<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\TextInput;
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
     * @return TextInput
     */
    public function createOtherName() {
        return (new TextInput(_('Jméno')));
    }

    /**
     * @return TextInput
     */
    public function createFamilyName() {
        return (new TextInput(_('Příjmení')));
    }

    /**
     * @return \Nette\Forms\Controls\BaseControl
     */
    public function createDisplayName() {
        return (new TextInput(_('Zobrazované jméno')))
            ->setOption('description', _('Pouze pokud je odlišené od "jméno příjmení".'));
    }

    /**
     * @return \Nette\Forms\Controls\BaseControl
     */
    public function createGender() {
        return (new RadioList(_('Pohlaví'), ['M' => 'muž', 'F' => 'žena']))
            ->setDefaultValue('M');
    }

    /**
     * @param $fieldName
     * @return \Nette\Forms\Controls\BaseControl|TextInput
     */
    public function createField($fieldName) {
        switch ($fieldName) {
            case 'other_name':
                return $this->createOtherName();
            case 'family_name':
                return $this->createFamilyName();
            case 'display_name':
                return $this->createDisplayName();
            case 'gender':
                return $this->createGender();
            default:
                throw new InvalidArgumentException();
        }
    }
}
