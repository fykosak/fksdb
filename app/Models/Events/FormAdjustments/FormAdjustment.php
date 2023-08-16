<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Forms\Form;

/**
 * @note If you write a form control validator be sure that you don't call
 * getValue on ReferencedId control -- it may cause a query out of transaction.
 * @template THolder of ModelHolder
 */
interface FormAdjustment
{
    /**
     * @phpstan-param THolder $holder
     */
    public function adjust(Form $form, ModelHolder $holder): void;
}
