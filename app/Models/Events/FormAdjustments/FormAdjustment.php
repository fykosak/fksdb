<?php

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\Holder;
use Nette\Forms\Form;

/**
 * @note If you write a form control validator be sure that you don't call
 * getValue on ReferencedId control -- it may cause a query out of transaction.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface FormAdjustment {

    public function adjust(Form $form, Holder $holder): void;
}
