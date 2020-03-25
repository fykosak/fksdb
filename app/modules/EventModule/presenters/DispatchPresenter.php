<?php

namespace EventModule;

use AuthenticatedPresenter;
use FKSDB\Components\Grids\Events\DispatchGrid;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class DispatchPresenter
 * @package EventModule
 */
class DispatchPresenter extends AuthenticatedPresenter {

    /** @return DispatchGrid */
    public function createComponentDispatchGrid(): DispatchGrid {
        /** @var ModelPerson $person */
        $person = $this->getUser()->getIdentity()->getPerson();
        return new DispatchGrid($person, $this->getContext());
    }

    public function titleDefault() {
        $this->setTitle(_('List of events'), 'fa fa-calendar');
    }

    /** @return array */
    public function getNavBarVariant(): array {
        return ['event', 'bg-dark navbar-dark'];
    }
}
