<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonFlag;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;

class FlagFactory
{
    /**
     * @phpstan-param array{required?:bool,caption?:string|null,description?:string|null} $metadata
     */
    public function createFlag(?HiddenField $hiddenField = null, array $metadata = []): BaseControl
    {
        $control = $this->createSpamMff();

        if ($metadata['required'] ?? false) {
            $conditioned = $control;
            if ($hiddenField) {
                $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
            }
            $conditioned->addRule(Form::FILLED, _('The field %label is required.'));
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

    public function createSpamMff(): PersonFlag
    {
        return new PersonFlag(_('I wish to receive information about MFF and the events they organize.'));
    }
}
