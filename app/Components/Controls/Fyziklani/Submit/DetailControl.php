<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Components\Controls\Helpers\AbstractDetailControl;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Diagnostics\Debugger;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @property FileTemplate $template
 */
class DetailControl extends AbstractDetailControl {
    /**
     * @var ModelFyziklaniSubmit
     */
    private $model;
    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * DetailControl constructor.
     * @param ITranslator $translator
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ITranslator $translator, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        parent::__construct($translator);
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @param ModelFyziklaniSubmit $submit
     */
    public function setSubmit(ModelFyziklaniSubmit $submit) {
        $this->model = $submit;
    }

    public function render() {
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'DetailControl.latte');
        $this->template->render();
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleCheck() {
        try {
            if ($this->model->canChange()) {
                $msg = $this->model->check($this->model->points);
                Debugger::log(\sprintf('fyziklani_submit %d checked by %d', $this->model->fyziklani_submit_id, $this->getPresenter()->getUser()->getIdentity()->getPerson()->person_id));

                $this->getPresenter()->flashMessage($msg, \BasePresenter::FLASH_SUCCESS);
                $this->redirect('this');
            }
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        } catch (PointsMismatchException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }
}
