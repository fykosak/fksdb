<?php

namespace FKSDB\Model\Transitions;

/**
 * Interface IStateModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IStateModel {

    public function getStateColumn(): string;

    public function getState(): ?string;
}
