<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\SelectBox;

class ChooserFactory extends AbstractFactory {

    public const FORMAT_KEY_VALUE = 'key-value';
    public const FORMAT_VALUE_META = 'value-meta';
    public const FORMAT_KEY_META = 'key-meta';

    private string $prompt;

    private OptionsProvider $optionsProvider;

    public function __construct(string $prompt, OptionsProvider $optionsProvider) {
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
}
