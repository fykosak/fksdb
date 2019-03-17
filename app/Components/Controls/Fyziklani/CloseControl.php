<?php


namespace FKSDB\Components\Controls\Fyziklani;


use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\model\Fyziklani\CloseSubmitStrategy;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class CloseControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class CloseControl extends Control {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * CloseControl constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ITranslator $translator
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, ITranslator $translator) {
        parent::__construct();
        $this->event = $event;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->translator = $translator;
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    private function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStrategy($this->event, $this->serviceFyziklaniTeam);
        $closeStrategy->closeGlobal($msg);
        $this->getPresenter()->flashMessage(Html::el()->add(Html::el('h3')->add('pořadí bylo uložené'))->add(Html::el('ul')->add($msg)), \BasePresenter::FLASH_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    /**
     * @param $category
     * @return FormControl
     */
    private function createComponentCloseCategoryForm(string $category): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addHidden('category', $category);
        $form->addSubmit('send', sprintf(_('Uzavřít kategorii %s.'), $category))->setDisabled(!$this->isReadyToClose($category));
        $form->onSuccess[] = function (Form $form) {
            $this->closeCategoryFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseGlobalForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uzavřít celé Fyziklání'))->setDisabled(!$this->isReadyToClose());
        $form->onSuccess[] = function () {
            $this->closeGlobalFormSucceeded();
        };
        return $control;
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryAForm(): FormControl {
        return $this->createComponentCloseCategoryForm('A');
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryBForm(): FormControl {
        return $this->createComponentCloseCategoryForm('B');
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryCForm(): FormControl {
        return $this->createComponentCloseCategoryForm('C');
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryFForm(): FormControl {
        return $this->createComponentCloseCategoryForm('F');
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws BadRequestException
     */
    public function closeCategoryFormSucceeded(Form $form) {
        $closeStrategy = new CloseSubmitStrategy($this->event, $this->serviceFyziklaniTeam);
        $closeStrategy->closeByCategory($form->getValues()->category, $msg);
        $this->getPresenter()->flashMessage(Html::el()->add(Html::el('h3')->add('pořadí bylo uložené'))->add(Html::el('ul')->add($msg)), \BasePresenter::FLASH_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    /**
     * @return CloseTeamsGrid
     */
    public function createComponentCloseGrid(): CloseTeamsGrid {
        return new CloseTeamsGrid($this->event, $this->serviceFyziklaniTeam);
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'CloseControl.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

    /**
     * @param null $category
     * @return bool
     */
    private function isReadyToClose($category = null): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }
}
