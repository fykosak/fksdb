<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Application\IJavaScriptCollector;
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

    private const SELECTOR_CLASS = 'autocomplete-select';
    private const PARAM_NAME = 'acName';
    private const INTERNAL_DELIMITER = ',';
    private const META_ELEMENT_SUFFIX = '__meta'; // must be same with constant in autocompleteSelect.js

    private IDataProvider $dataProvider;

    private bool $ajax;

    private bool $multiSelect = false;

    private string $ajaxUrl;

    /**
     * Body of JS function(ul, item) returning jQuery element.
     *
     * @see http://api.jqueryui.com/autocomplete/#method-_renderItem
     * @var string|null
     */
    private ?string $renderMethod;

    private bool $attachedJSON = false;

    private bool $attachedJS = false;

    public function __construct(bool $ajax, ?string $label = null, ?string $renderMethod = null) {
        parent::__construct($label);

        $this->monitor(IAutocompleteJSONProvider::class, function (IAutocompleteJSONProvider $provider) {
            if (!$this->attachedJSON) {
                $this->attachedJSON = true;
                $name = $this->lookupPath(IAutocompleteJSONProvider::class);
                $this->ajaxUrl = $provider->link('autocomplete!', [
                    self::PARAM_NAME => $name,
                ]);
            }
        });
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $collector->registerJSFile('js/autocompleteSelect.js');
            }
        });

        $this->ajax = $ajax;
        $this->renderMethod = $renderMethod;
    }

    public function getDataProvider(): ?IDataProvider {
        return $this->dataProvider ?? null;
    }

    public function getRenderMethod(): ?string {
        return $this->renderMethod;
    }

    public function isAjax(): bool {
        return $this->ajax;
    }

    public function isMultiSelect(): bool {
        return $this->multiSelect;
    }

    public function setDataProvider(IDataProvider $dataProvider): void {
        if ($this->ajax && !($dataProvider instanceof IFilteredDataProvider)) {
            throw new InvalidArgumentException('Data provider for AJAX must be instance of IFilteredDataProvider.');
        }
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setDefaultValue($this->getValue());
    }

    public function getControl(): Html {
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

    public function loadHttpData(): void {
        $path = explode('[', strtr(str_replace(['[]', ']'], '', $this->getHtmlName()), '.', '_'));
        $metaPath = $path;
        $metaPath[count($metaPath) - 1] .= self::META_ELEMENT_SUFFIX;
        try {
            $wasSent = Arrays::get($this->getForm()->getHttpData(), $path);
        } catch (InvalidArgumentException $exception) {
            $wasSent = false;
        }
        if ($wasSent && !Arrays::get($this->getForm()->getHttpData(), $metaPath, null)) {
            $this->addError(sprintf(_('Field %s requires JavaScript enabled.'), $this->caption));
            $this->setValue(null);
        } else {
            parent::loadHttpData();
        }
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function setValue($value): self {
        if ($this->isMultiSelect()) {
            if (is_array($value)) {
                $this->value = $value;
            } elseif ($value === '') {
                $this->value = [];
            } else {
                $this->value = explode(self::INTERNAL_DELIMITER, $value);
            }
        } elseif ($value === '') {
            $this->value = null;
        } else {
            $this->value = $value;
        }
        if (isset($this->dataProvider)) {
            $this->dataProvider->setDefaultValue($this->value);
        }
        return $this;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function setDefaultValue($value): self {
        if (isset($this->dataProvider)) {
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

    public function setMultiSelect(bool $multiSelect): void {
        $this->multiSelect = $multiSelect;
    }
}
