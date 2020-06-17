<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

/**
 * Class CreateForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CreateForm extends AbstractForm {
    /**
     * @var int
     */
    private $year;
    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * CreateForm constructor.
     * @param Container $container
     * @param ModelContest $contest
     * @param int $year
     */
    public function __construct(Container $container, ModelContest $contest, int $year) {
        parent::__construct($contest, $container, true);
        $this->year = $year;
    }

    /**
     * @param ServiceEvent $serviceEvent
     * @return void
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $values = $form->getValues(true);
        $data = \FormUtils::emptyStrToNull($values[self::CONT_EVENT], true);
        $data['year'] = $this->year;
        $model = $this->serviceEvent->createNewModel($data);

        $this->updateTokens($model);
        $this->flashMessage(sprintf(_('Akce %s uložena.'), $model->name), ILogger::SUCCESS);

        $this->getPresenter()->redirect('list'); // if there's no backlink
    }
}
