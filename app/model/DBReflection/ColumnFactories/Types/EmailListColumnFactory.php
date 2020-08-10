<?php

namespace FKSDB\DBReflection\ColumnFactories;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EmailRow
 * @package FKSDB\Components\DatabaseReflection
 */
class EmailListColumnFactory extends DefaultColumnFactory {

    private int $maxLength = -1;

    public function setMaxLength(int $maxLength): void {
        $this->maxLength = $maxLength;
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->addCondition(Form::FILLED)
            ->addRule(function (string $value): bool {
                $values = mailparse_rfc822_parse_addresses($value);
                if (!count($values)) {
                    // is filled but no emails
                    return false;
                }
                if ($this->maxLength == -1) {
                    // length doesnt matter
                    return true;
                }
                if (count($values) <= $this->maxLength) {
                    // check length
                    return true;
                }
                return false;
            });
        return $control;
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $rows = mailparse_rfc822_parse_addresses($model->{$this->getModelAccessKey()});
        if (!count($rows)) {
            return NotSetBadge::getHtml();
        }
        $container = Html::el('span');
        foreach ($rows as ['address' => $address, 'display' => $display]) {
            $container->addHtml(Html::el('a')
                ->addAttributes(['href' => 'mailto:' . $address])
                ->addText($display . ' <' . $address . '>')
            );
        }
        return $container;
    }
}
