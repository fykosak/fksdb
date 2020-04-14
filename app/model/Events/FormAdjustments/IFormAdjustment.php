<?php

namespace FKSDB\Events\FormAdjustments;

use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use Nette\Forms\Form;

/**
 * @note If you write a form control validator be sure that you don't call
 * getValue on ReferencedId control -- it may cause a query out of transaction.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IFormAdjustment {

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     */
    public function adjust(Form $form, Machine $machine, Holder $holder);
}

