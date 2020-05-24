<?php

namespace FKSDB\Components\Controls\Entity\Event;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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
     * CreateForm constructor.
     * @param Container $container
     * @param ModelContest $contest
     * @param int $year
     * @throws BadRequestException
     * @throws \Exception
     */
    public function __construct(Container $container, ModelContest $contest, int $year) {
        parent::__construct($container);
        $this->year = $year;

        $form = $this->createBaseForm($contest);

        $form->addSubmit('send', _('Create'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form);
        };
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleFormSuccess(Form $form) {
        /** @var ServiceEvent $serviceEvent */
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $values = $form->getValues();
        $data = \FormUtils::emptyStrToNull($values[self::CONT_EVENT]);
        $data['year'] = $this->year;
        $model = $serviceEvent->createNewModel($data);

        $this->updateTokens($model);
        $this->flashMessage(sprintf(_('Akce %s uložena.'), $model->name), ILogger::SUCCESS);

        $this->getPresenter()->redirect('list'); // if there's no backlink
    }
}
