<?php

/**
 * For presenters that provide series no. context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */

namespace FKSDB\CoreModule;

use IContestPresenter;
use Nette\Application\BadRequestException;

/**
 * Interface ISeriesPresenter
 */
interface ISeriesPresenter extends IContestPresenter {

    /**
     * @return int
     * @throws BadRequestException
     */
    public function getSelectedSeries();
}

