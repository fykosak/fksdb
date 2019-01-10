<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Forms\Controls\TextBase;
use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;

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
    const INTERNAL_DELIMITER = ',';
    const META_ELEMENT_SUFFIX = '__meta'; // must be same with constant in autocompleteSelect.js

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
    private $multiSelect;

    /**
     * @var string
     */
    private $ajaxUrl;

    /**
     * Body of JS function(ul, item) returning jQuery element.
     *
     * @see http://api.jqueryui.com/autocomplete/#method-_renderItem
     * @var string
     */
    private $renderMethod;

    function __construct($ajax, $label = null, $renderMethod = null) {
        parent::__construct($label);

        $this->monitor('FKSDB\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider');
        $this->monitor('FKSDB\Application\IJavaScriptCollector');
        $this->ajax = $ajax;
        $this->renderMethod = $renderMethod;
    }

    private $attachedJSON = false;
    private $attachedJS = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJSON && $obj instanceof IAutocompleteJSONProvider) {
            $this->attachedJSON = true;
            $name = $this->lookupPath('FKSDB\Components\Forms\Controls\Autocomplete\IAutocompleteJSONProvider');

            $this->ajaxUrl = $obj->link('autocomplete!', array(
                self::PARAM_NAME => $name,
            ));
        }
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/autocompleteSelect.js');
        }
    }

    /**
     * @return IDataProvider
     */
    public function getDataProvider(): IDataProvider {
        return $this->dataProvider;
    }

    public function getRenderMethod() {
        return $this->renderMethod;
    }

    public function isAjax() {
        return $this->ajax;
    }

    public function isMultiSelect() {
        return $this->multiSelect;
    }

    public function setDataProvider(IDataProvider $dataProvider) {
        if ($this->ajax && !($dataProvider instanceof IFilteredDataProvider)) {
            throw new InvalidArgumentException('Data provider for AJAX must be instance of IFilteredDataProvider.');
        }
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setDefaultValue($this->getValue());
    }

    public function getControl() {
        $control = parent::getControl();

        $control->data['ac'] = (int)true;
        $control->data['ac-ajax'] = (int)$this->isAjax();
        $control->data['ac-multiselect'] = (int)$this->isMultiselect();
        $control->data['ac-ajax-url'] = $this->ajaxUrl;
        $control->data['ac-render-method'] = $this->renderMethod;

        $control->addClass(self::SELECTOR_CLASS);

        $defaultValue = $this->getValue();
        if ($defaultValue) {
            if ($this->isMultiselect()) {
                $defaultTextValue = [];
                foreach ($defaultValue as $id) {
                    $defaultTextValue[] = $this->getDataProvider()->getItemLabel($id);
                }
                $defaultTextValue = json_encode($defaultTextValue);
                $control->value = implode(self::INTERNAL_DELIMITER, $defaultValue);
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

    public function loadHttpData() {
        $path = explode('[', strtr(str_replace(array('[]', ']'), '', $this->getHtmlName()), '.', '_'));
        $meta_path = $path;
        $meta_path[count($meta_path) - 1] .= self::META_ELEMENT_SUFFIX;
        try {
            $wasSent = Arrays::get($this->getForm()->getHttpData(), $path);
        } catch (InvalidArgumentException $e) {
            $wasSent = false;
        }
        if ($wasSent && !Arrays::get($this->getForm()->getHttpData(), $meta_path, null)) {
            $this->addError(sprintf(_('Políčko %s potřebuje povolený Javascript.'), $this->caption));
            $this->setValue(null);
        } else {
            parent::loadHttpData();
        }
    }

    public function setValue($value) {
        if ($this->isMultiselect()) {
            if (is_array($value)) {
                $this->value = $value;
            } else if ($value === '') {
                $this->value = [];
            } else {
                $this->value = explode(self::INTERNAL_DELIMITER, $value);
            }
        } else {
            if ($value === '') {
                $this->value = null;
            } else {
                $this->value = $value;
            }
        }
        if ($this->dataProvider) {
            $this->dataProvider->setDefaultValue($this->value);
        }
    }

    public function setDefaultValue($value) {
        if ($this->dataProvider) {
            $this->dataProvider->setDefaultValue($value);
        }
        return parent::setDefaultValue($value);
    }

    public function getValue() {
        return $this->value;
    }

    public function setMultiSelect($multiSelect) {
        $this->multiSelect = $multiSelect;
    }

}
