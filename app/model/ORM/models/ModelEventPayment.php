<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Events\Payment\Machine;
use Events\Payment\Transition;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property integer person_id
 * @property ActiveRow person
 * @property integer payment_id
 * @property ActiveRow event
 * @property integer event_id
 * @property string data
 * @property string state
 * @property float price_kc
 * @property float price_eur
 * @property string constant_symbol
 * @property string variable_symbol
 * @property string specific_symbol
 */
class ModelEventPayment extends AbstractModelSingle implements IResource {
    const STATE_WAITING = 'waiting';
    const STATE_CONFIRMED = 'confirmed';
    const STATE_CANCELED = 'canceled';

    /**
     * @var Machine
     */
    private $machine;

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function getResourceId(): string {
        return 'payment';
    }

    public function createMachine() {
        $this->machine = new Machine();
        $this->machine->setInitState(self::STATE_WAITING);

        $transition = new Transition(null, self::STATE_WAITING, _('Vytvorit platbu'));
        $this->machine->addTransition($transition);

        $transition = new Transition(self::STATE_WAITING, self::STATE_CONFIRMED, _('Zaplatil'));
        $this->machine->addTransition($transition);

        $transition = new Transition(self::STATE_WAITING, self::STATE_CANCELED, _('Zrusit platbu'));
        $this->machine->addTransition($transition);

        return $this->machine;
    }

    public function getMachine(): Machine {
        return $this->machine;
    }

}
