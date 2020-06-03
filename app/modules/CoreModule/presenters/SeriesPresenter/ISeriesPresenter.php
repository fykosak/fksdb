<?php

/**
 * For presenters that provide series no. context.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */

namespace FKSDB\CoreModule\SeriesPresenter;

use IContestPresenter;

/**
 * Interface ISeriesPresenter
 */
interface ISeriesPresenter extends IContestPresenter {

    public function getSelectedSeries(): ?int;
}
