<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Model\Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\IControl;

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

    /**
     * @param BaseControl|IComponent $component
     * @param Field $field
     * @return void
     */
    protected function setDefaultValue(IComponent $component, Field $field): void {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param BaseControl|IComponent $component
     * @return void
     */
    protected function setDisabled(IComponent $component): void {
        $component->setDisabled();
    }

    /**
     * @param Component|IComponent $component
     * @return Component|IControl
     */
    public function getMainControl(IComponent $component): IControl {
        return $component;
    }
}
