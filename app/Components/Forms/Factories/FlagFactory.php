<?php

namespace FKSDB\Components\Forms\Factories;

use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\Utils\Arrays;


/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class FlagFactory {
    
    public function createFlag($fid, $acYear, HiddenField $hiddenField = null, $metadata = array()) {
        $methodName = 'create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $fid)));
        $control = call_user_func(array($this, $methodName), $acYear);

        if (Arrays::get($metadata, 'required', false)) {
            $conditioned = $control;
            if ($hiddenField) {
                $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
            }
            $conditioned->addRule(Form::FILLED, _('Pole %label je povinné.'));
        }
        if ($caption = Arrays::get($metadata, 'caption', null)) { // intentionally =
            $control->caption = $caption;
        }
        if ($description = Arrays::get($metadata, 'description', null)) { // intentionally =
            $control->setOption('description', $description);
        }
        return $control;
    }
    
    public function createSpamMff($acYear = null) {
        return (new Checkbox(_('Přeji si dostávat informace o dění na MFF a akcích, které pořádáme')));
    }
    
}

