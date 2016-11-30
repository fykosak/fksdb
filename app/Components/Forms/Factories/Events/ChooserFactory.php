<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\SelectBox;

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

    function __construct($prompt,IOptionsProvider $optionsProvider) {
        $this->prompt = $prompt;
        $this->optionsProvider = $optionsProvider;
    }

    protected function createComponent(Field $field,BaseMachine $machine,Container $container) {

        $component = new SelectBox($field->getLabel());
        $component->setOption('description',$field->getDescription());

        $component->setPrompt($this->prompt);

        $options = $this->optionsProvider->getOptions($field);
        $opts = [];
        foreach ($options as $key => $option) {
            if(is_array($option)){
                $opts[$option['value']] = $option['label'];
            }else{
                $opts[$key] = $option;
            }
        }

        $component->setItems($opts);

        return $component;
    }

    protected function setDefaultValue($component,Field $field,BaseMachine $machine,Container $container) {
        $component->setDefaultValue($field->getValue());
    }

    protected function setDisabled($component,Field $field,BaseMachine $machine,Container $container) {
        $component->setDisabled();
    }

    public function getMainControl(Component $component) {
        return $component;
    }

}
