<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use Nette\Application\UI\Presenter;
use Nette\Forms\Controls\TextBase;
use Nette\InvalidArgumentException;
use Nette\NotImplementedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @todo Implement AJAX loading 
 *       Should return school_id or null.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AutocompleteSelectBox extends TextBase {

    const SELECTOR_CLASS = 'autocompleteSelect';
    const PARAM_SEARCH = 'acQ';
    const PARAM_NAME = 'acName';
    const INTERNAL_DELIMITER  = ',';

    /**
     * @var IDataProvider
     */
    private $dataProvider;

    /**
     * @var boolean
     */
    private $ajax;

    /**
     * @var boolean
     */
    private $multiselect;

    /**
     * @var string
     */
    private $ajaxUrl;

    function __construct($ajax, $label = null) {
        parent::__construct($label);
        $this->monitor('Nette\Application\UI\Presenter');
        $this->ajax = $ajax;
    }

    /**
     * @return IDataProvider
     */
    public function getDataProvider() {
        return $this->dataProvider;
    }

    public function isAjax() {
        return $this->ajax;
    }

    public function isMultiselect() {
        return $this->multiselect;
    }

    public function setDataProvider(IDataProvider $dataProvider) {
        if ($this->ajax && !($dataProvider instanceof IFilteredDataProvider)) {
            throw new InvalidArgumentException('Data provider for AJAX must be instance of IFilteredDataProvider.');
        }
        $this->dataProvider = $dataProvider;
    }

    public function getControl() {
        $control = parent::getControl();

        $control->data['ac-ajax'] = (int) $this->isAjax();
        $control->data['ac-multiselect'] = (int) $this->isMultiselect();
        $control->data['ac-ajax-url'] = $this->ajaxUrl;

        $control->addClass(self::SELECTOR_CLASS);

        $defaultValue = $this->getValue();
        if ($defaultValue) {
            if ($this->isMultiselect()) {
                $defaultTextValue = array();
                foreach ($defaultValue as $id) {
                    $defaultTextValue[] = $this->getDataProvider()->getItemLabel($id);
                }
                $defaultTextValue = json_encode($defaultTextValue);
                $control->value = implode(self::INTERNAL_DELIMITER , $defaultValue);
            } else {
                $defaultTextValue = $this->getDataProvider()->getItemLabel($defaultValue);
                $control->value = $defaultValue;
            }
            $control->data['ac-default-value'] = $defaultTextValue;
        }

        if (!$this->isAjax()) {
            $control->data['ac-items'] = json_encode($this->getDataProvider()->getItems());
        }

        return $control;
    }

    public function setValue($value) {
        if ($this->isMultiselect()) {
            $this->value = explode(self::INTERNAL_DELIMITER , $value);
        } else {
            $this->value = $value;
        }
    }

    public function getValue() {
        return $this->value;
    }

    public function monitor($type) {
        parent::monitor($type);
    }

    protected function attached($presenter) {
        if ($presenter instanceof Presenter) {
            $name = $this->lookupPath('Nette\Application\UI\Presenter');

            $this->ajaxUrl = $presenter->link('autocomplete!', array(
                self::PARAM_NAME => $name,
            ));
        }
        parent::attached($presenter);
    }

    public function setMultiselect($multiselect) {
        $this->multiselect = $multiselect;
    }

    public function setItems(array $items, $useKeys = TRUE) {
        throw new NotImplementedException('Use setDataProvider instead.');
    }

    private function getItemLabel($item) {
        return $item[IDataProvider::LABEL];
    }

}
