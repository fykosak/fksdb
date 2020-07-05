<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonFlag;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;


/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class FlagFactory {

    /**
     * @param HiddenField|null $hiddenField
     * @param array $metadata
     * @return BaseControl
     */
    public function createFlag(HiddenField $hiddenField = null, $metadata = []): BaseControl {
        $control = $this->createSpamMff();

        if ($metadata['required'] ?? false) {
            $conditioned = $control;
            if ($hiddenField) {
                $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
            }
            $conditioned->addRule(Form::FILLED, _('Pole %label je povinné.'));
        }
        $caption = $metadata['caption'] ?? null;
        if ($caption) { // intentionally =
            $control->caption = $caption;
        }
        $description = $metadata['description'] ?? null;
        if ($description) { // intentionally =
            $control->setOption('description', $description);
        }
        return $control;
    }

    public function createSpamMff(): PersonFlag {
        return new PersonFlag(_('Přeji si dostávat informace o dění na MFF a akcích, které pořádáme'));
    }
}
