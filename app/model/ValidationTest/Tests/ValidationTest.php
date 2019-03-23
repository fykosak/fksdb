<?php

namespace FKSDB\ValidationTest;


use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Utils\Html;

/**
 * Class ValidationTest
 */
abstract class ValidationTest {
    /**
     * @param ModelPerson $person
     * @return ValidationLog[]
     */
    abstract static function run(ModelPerson $person): array;

    /**
     * @return string
     */
    abstract function getTitle(): string;

    /**
     * @return string
     */
    abstract function getAction(): string;

    /**
     * @param ValidationGrid $grid
     * @return void
     */
    abstract static function configureGrid(ValidationGrid $grid);

    /**
     * @param ValidationLog $log
     * @return Html
     */
    protected static function createHtml(ValidationLog $log): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-' . $log->level])->add($log->message);

    }
}
