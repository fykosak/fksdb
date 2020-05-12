<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextBase;
use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;
use Nette\Utils\Html;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @todo Implement AJAX loading
 *       Should return school_id or null.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AutocompleteSelectBox extends TextBase {

    const SELECTOR_CLASS = 'autocomplete-select';
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

    /**
     * AutocompleteSelectBox constructor.
     * @param $ajax
     * @param string $label
     * @param string $renderMethod
     */
    function __construct(bool $ajax, string $label = null, string $renderMethod = null) {
        parent::__construct($label);

        $this->monitor(IAutocompleteJSONProvider::class);
        $this->monitor(IJavaScriptCollector::class);
        $this->ajax = $ajax;
        $this->renderMethod = $renderMethod;
    }

    private $attachedJSON = false;
    private $attachedJS = false;

    /**
     * @param $obj
     */
    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJSON && $obj instanceof IAutocompleteJSONProvider) {
            $this->attachedJSON = true;
            $name = $this->lookupPath(IAutocompleteJSONProvider::class);

            $this->ajaxUrl = $obj->link('autocomplete!', [
                self::PARAM_NAME => $name,
            ]);
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

    /**
     * @return null|string
     */
    public function getRenderMethod() {
        return $this->renderMethod;
    }

    /**
     * @return bool
     */
    public function isAjax() {
        return $this->ajax;
    }

    /**
     * @return bool
     */
    public function isMultiSelect() {
        return $this->multiSelect;
    }

    /**
     * @param IDataProvider $dataProvider
     */
    public function setDataProvider(IDataProvider $dataProvider) {
        if ($this->ajax && !($dataProvider instanceof IFilteredDataProvider)) {
            throw new InvalidArgumentException('Data provider for AJAX must be instance of IFilteredDataProvider.');
        }
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setDefaultValue($this->getValue());
    }

    /**
     * @return Html
     */
    public function getControl() {
        $control = parent::getControl();
        $control->addAttributes([
            'data-ac' => (int)true,
            'data-ac-ajax' => (int)$this->isAjax(),
            'data-ac-multiselect' => (int)$this->isMultiSelect(),
            'data-ac-ajax-url' => $this->ajaxUrl,
            'data-ac-render-method' => $this->getRenderMethod(),
            'class' => self::SELECTOR_CLASS . ' form-control',
        ]);

        $defaultValue = $this->getValue();
        if ($defaultValue) {
            if ($this->isMultiSelect()) {
                $defaultTextValue = [];
                foreach ($defaultValue as $id) {
                    $defaultTextValue[] = $this->getDataProvider()->getItemLabel($id);
                }
                $defaultTextValue = json_encode($defaultTextValue);
                $control->addAttributes([
                    'value' => implode(self::INTERNAL_DELIMITER, $defaultValue),
                ]);
            } else {
                $defaultTextValue = $this->getDataProvider()->getItemLabel($defaultValue);
                $control->addAttributes([
                    'value' => $defaultValue,
                ]);
            }
            $control->addAttributes([
                'data-ac-default-value' => $defaultTextValue,
            ]);
        }

        if (!$this->isAjax()) {
            $control->addAttributes([
                'data-ac-items' => json_encode($this->getDataProvider()->getItems()),
            ]);
        }

        return $control;
    }

    public function loadHttpData() {
        $path = explode('[', strtr(str_replace(['[]', ']'], '', $this->getHtmlName()), '.', '_'));
        $metaPath = $path;
        $metaPath[count($metaPath) - 1] .= self::META_ELEMENT_SUFFIX;
        try {
            $wasSent = Arrays::get($this->getForm()->getHttpData(), $path);
        } catch (InvalidArgumentException $exception) {
            $wasSent = false;
        }
        if ($wasSent && !Arrays::get($this->getForm()->getHttpData(), $metaPath, null)) {
            $this->addError(sprintf(_('Políčko %s potřebuje povolený Javascript.'), $this->caption));
            $this->setValue(null);
        } else {
            parent::loadHttpData();
        }
    }

    /**
     * @param $value
     * @return TextBase
     */
    public function setValue($value) {
        if ($this->isMultiSelect()) {
            if (is_array($value)) {
                $this->value = $value;
            } elseif ($value === '') {
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
        return $this;
    }

    /**
     * @param $value
     * @return BaseControl
     */
    public function setDefaultValue($value) {
        if ($this->dataProvider) {
            $this->dataProvider->setDefaultValue($value);
        }
        return parent::setDefaultValue($value);
    }

    /**
     * @return mixed|string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param $multiSelect
     */
    public function setMultiSelect($multiSelect) {
        $this->multiSelect = $multiSelect;
    }

}
