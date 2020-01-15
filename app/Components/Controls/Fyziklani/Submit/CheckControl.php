<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use BasePresenter;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @property FileTemplate $template
 */
class CheckControl extends Control {
    /**
     * @var ITranslator
     */
    protected $translator;

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
        parent::__construct();
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->translator = $translator;
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
     * @throws AbortException
     */
    public function handleCheck() {
        try {
            $log = $this->serviceFyziklaniSubmit->checkSubmit($this->model, $this->model->points, $this->getPresenter()->getUser());
            $this->getPresenter()->flashMessage($log->getMessage(), $log->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        } catch (PointsMismatchException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }

}
