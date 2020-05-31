<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * Class AbstractValuePrinter
 * *
 */
abstract class AbstractValuePrinter {
    /**
     * @param null $value
     * @return Html
     */
    abstract protected function getHtml($value): Html;

    /**
     * @param mixed $value
     * @return Html
     */
    public function __invoke($value): Html {
        if (\is_null($value)) {
            return $this->getEmptyValueHtml();
        }
        return $this->getHtml($value);
    }

    protected function getEmptyValueHtml(): Html {
        return NotSetBadge::getHtml();
    }
}
