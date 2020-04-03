<?php

namespace FKSDB\CoreModule;


use Nette\Application\BadRequestException;

/**
 * For presenters that provide series no. context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
interface ISeriesPresenter extends IContestPresenter {

    /**
     * @return int
     * @throws BadRequestException
     */
    public function getSelectedSeries(): int;
}

