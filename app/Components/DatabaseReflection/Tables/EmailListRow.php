<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EmailRow
 * @package FKSDB\Components\DatabaseReflection
 */
class EmailListRow extends DefaultRow {
    /**
     * @var int
     */
    private $maxLength = -1;

    /**
     * @param int $maxLength
     */
    public function setMaxLength(int $maxLength) {
        $this->maxLength = $maxLength;
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
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

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $rows = mailparse_rfc822_parse_addresses($model->{$this->getModelAccessKey()});
        if (!count($rows)) {
            return NotSetBadge::getHtml();
        }
        $container = Html::el('span');
        foreach ($rows as list('address' => $address, 'display' => $display)) {
            $container->addHtml(Html::el('a')
                ->addAttributes(['href' => 'mailto:' . $address])
                ->addText($display . ' <' . $address . '>')
            );
        }
        return $container;
    }
}
