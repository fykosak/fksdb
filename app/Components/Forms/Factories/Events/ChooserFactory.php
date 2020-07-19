<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\Field;
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

    const FORMAT_KEY_VALUE = 'key-value';
    const FORMAT_VALUE_META = 'value-meta';
    const FORMAT_KEY_META = 'key-meta';

    /**
     * @var string
     */
    private $prompt;

    /**
     * @var IOptionsProvider
     */
    private $optionsProvider;

    /**
     * ChooserFactory constructor.
     * @param string $prompt
     * @param IOptionsProvider $optionsProvider
     */
    public function __construct($prompt, IOptionsProvider $optionsProvider) {
        $this->prompt = $prompt;
        $this->optionsProvider = $optionsProvider;
    }

    /**
     * @param Field $field
     * @return SelectBox
     */
    public function createComponent(Field $field): IComponent {

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
    protected function setDefaultValue(IComponent $component, Field $field) {
        $component->setDefaultValue($field->getValue());
    }

    /**
     * @param BaseControl|IComponent $component
     * @return void
     */
    protected function setDisabled(IComponent $component) {
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
