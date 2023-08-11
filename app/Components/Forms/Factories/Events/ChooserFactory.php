<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Models\Events\Model\Holder\Field;
use Nette\Forms\Controls\SelectBox;

class ChooserFactory extends AbstractFactory
{
    private string $prompt;

    private OptionsProvider $optionsProvider;

    public function __construct(string $prompt, OptionsProvider $optionsProvider)
    {
        $this->prompt = $prompt;
        $this->optionsProvider = $optionsProvider;
    }

    public function createComponent(Field $field): SelectBox
    {

        $component = new SelectBox($field->label);
        $component->setOption('description', $field->description);

        $component->setPrompt($this->prompt);

        $options = $this->optionsProvider->getOptions($field);
        $opts = [];
        foreach ($options as $key => $option) {
            /** @phpstan-ignore-next-line */
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
