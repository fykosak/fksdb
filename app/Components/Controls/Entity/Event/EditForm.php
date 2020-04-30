<?php

namespace FKSDB\Components\Controls\Entity\Event;

use Events\Model\Holder\Holder;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Config\NeonScheme;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Neon\Neon;
use Nette\Utils\Html;
use Nette\Neon\Exception;

/**
 * Class EditControl
 * @package FKSDB\Components\Controls\Entity\Event
 */
class EditForm extends AbstractForm implements IEditEntityForm {

    /**
     * @var ModelEvent
     */
    private $model;

    /**
     * EditControl constructor.
     * @param Container $container
     * @param ModelContest $contest
     * @throws BadRequestException
     * @throws \Exception
     */
    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container);

        $form = $this->createBaseForm($contest);
        $form->addSubmit('send', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form);
        };
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @throws BadRequestException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([
            self::CONT_EVENT => $model->toArray(),
        ]);
        /** @var TextArea $paramControl */
        $paramControl = $this->getForm()->getComponent(self::CONT_EVENT)->getComponent('parameters');
        $paramControl->setOption('description', $this->createParamDescription());
        $paramControl->addRule(function (BaseControl $control) {
            /** @var Holder $holder */
            $holder = $this->container->createEventHolder($this->model);
            $scheme = $holder->getPrimaryHolder()->getParamScheme();
            $parameters = $control->getValue();
            try {
                if ($parameters) {
                    $parameters = Neon::decode($parameters);
                } else {
                    $parameters = [];
                }
                NeonScheme::readSection($parameters, $scheme);
                return true;
            } catch (Exception $exception) {
                $control->addError($exception->getMessage());
                return false;
            }
        }, _('Parametry nesplňují Neon schéma'));
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = \FormUtils::emptyStrToNull($values[self::CONT_EVENT]);
        $model = $this->model;

        /** @var ServiceEvent $serviceEvent */
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $serviceEvent->updateModel2($model, $data);

        $this->updateTokens($model);

        $this->flashMessage(sprintf(_('Akce %s uložena.'), $model->name), ILogger::SUCCESS);
        $this->getPresenter()->redirect('list');
    }


    /**
     * @return Html
     */
    private function createParamDescription() {
        /** @var Holder $holder */
        $holder = $this->container->createEventHolder($this->model);
        $scheme = $holder->getPrimaryHolder()->getParamScheme();
        $result = Html::el('ul');
        foreach ($scheme as $key => $meta) {
            $item = Html::el('li');
            $result->addText($item);

            $item->addHtml(Html::el(null)->setText($key));
            if (isset($meta['default'])) {
                $item->addText(': ');
                $item->addHtml(Html::el(null)->setText(\Utils::getRepr($meta['default'])));
            }
        }
        return $result;
    }
}
