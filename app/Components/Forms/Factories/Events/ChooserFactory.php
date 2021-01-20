<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ChooserFactory extends AbstractFactory {

    public const FORMAT_KEY_VALUE = 'key-value';
    public const FORMAT_VALUE_META = 'value-meta';
    public const FORMAT_KEY_META = 'key-meta';

    private string $prompt;

    private IOptionsProvider $optionsProvider;

    public function __construct(string $prompt, IOptionsProvider $optionsProvider) {
        $this->prompt = $prompt;
        $this->optionsProvider = $optionsProvider;
    }

    public function createComponent(Field $field): SelectBox {

        $component = new SelectBox($field->getLabel());
        $component->setOption('description', $field->getDescription());

        $component->setPrompt($this->prompt);

        $options = $this->optionsProvider->getOptions($field);
        $opts = [];
        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $opts[$option['value']] = $option['label'];
            } else {
                $opts[$key] = $option;
            }
        }

        $component->setItems($opts);

        return $component;
    }

    protected function setDefaultValue(BaseControl $control, Field $field): void {
        $control->setDefaultValue($field->getValue());
    }

    protected function setDisabled(BaseControl $control): void {
        $control->setDisabled();
        $control->setOmitted(false);
    }
}
