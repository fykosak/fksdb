<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use BasePresenter;
use Exception;
use FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Tracy\Debugger;
use function sprintf;

/**
 * Class DetailControl
 * @property FileTemplate $template
 */
class DetailControl extends Control {
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
            $log = $this->model->check($this->model->points);
            Debugger::log(sprintf('fyziklani_submit %d checked by %d', $this->model->fyziklani_submit_id, $this->getPresenter()->getUser()->getIdentity()->getPerson()->person_id));
            $this->getPresenter()->flashMessage($log->getMessage(), $log->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        } catch (PointsMismatchException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        } catch (Exception $exception) {
            Debugger::log($exception);
            $this->getPresenter()->flashMessage('Error!', BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }

    /**
     * @return StringValueControl
     */
    public function createComponentStringValue(): StringValueControl {
        return new StringValueControl($this->translator);
    }
}
