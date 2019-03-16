<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use ModelException;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use PublicModule\BasePresenter;
use ServiceSubmit;
use Submits\FilesystemSubmitStorage;
use Submits\StorageException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SubmitsGrid extends BaseGrid {

    /** @var ServiceSubmit */
    private $submitService;

    /** @var FilesystemSubmitStorage */
    private $submitStorage;

    /**
     * @var ModelContestant
     */
    private $contestant;

    /**
     * SubmitsGrid constructor.
     * @param ServiceSubmit $submitService
     * @param FilesystemSubmitStorage $submitStorage
     * @param ModelContestant $contestant
     */
    function __construct(ServiceSubmit $submitService, FilesystemSubmitStorage $submitStorage, ModelContestant $contestant) {
        parent::__construct();

        $this->submitService = $submitService;
        $this->submitStorage = $submitStorage;
        $this->contestant = $contestant;
    }

    /**
     * @param $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $submits = $this->submitService->getSubmits();
        $submits->where('ct_id = ?', $this->contestant->ct_id); //TODO year + contest?

        $this->setDataSource(new NDataSource($submits));
        $this->setDefaultOrder('series DESC, tasknr ASC');

        //
        // columns
        //
        $this->addColumn('task', _('Úloha'))
                ->setRenderer(function($row) use($presenter) {
                            $row->task_id; // stupid caching...
                            $task = $row->getTask();
                            $FQname = $task->getFQName();

                            if ($row->source == ModelSubmit::SOURCE_UPLOAD) {
                                $el = Html::el('a');
                                $el->href = $presenter->link(':Public:Submit:download', array('id' => $row->submit_id));
                                $el->setText($FQname);
                                return $el;
                            } else {
                                return $FQname;
                            }
                        });
        $this->addColumn('submitted_on', _('Čas odevzdání'));
        $this->addColumn('source', _('Způsob odevzdání'));

        //
        // operations
        //
        $this->addButton('revoke', _('Zrušit'))
                ->setClass('btn btn-xs btn-warning')
                ->setText(_('Zrušit'))
                ->setShow(function($row) {
                            return $this->canRevoke($row);
                        })
                ->setLink(function($row) {
                            return $this->link('revoke!', $row->submit_id);
                        })
                ->setConfirmationDialog(function($row) {
                            return \sprintf(_('Opravdu vzít řešení úlohy %s zpět?'),$row->getTask()->getFQName());
                        });



        //
        // appeareance
        //
        $this->paginate = false;
        $this->enableSorting = false;
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function handleRevoke($id) {
        $submit = $this->submitService->findByPrimary($id);

        if (!$submit) {
            throw new BadRequestException('Neexistující submit.', 404);
        }


//        $submit->task_id; // stupid touch
        $contest = $submit->getContestant()->getContest();
        if (!$this->presenter->getContestAuthorizator()->isAllowed($submit, 'revoke', $contest)) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }

        if (!$this->canRevoke($submit)) {
            throw new BadRequestException('Nelze zrušit submit.', 403);
        }

        try {
            $this->submitStorage->deleteFile($submit);
            $this->submitService->dispose($submit);
            $this->flashMessage(sprintf('Odevzdání úlohy %s zrušeno.', $submit->getTask()->getFQName()), BasePresenter::FLASH_SUCCESS);
            $this->redirect('this');
        } catch (StorageException $e) {
            $this->flashMessage(sprintf('Během mazání úlohy %s došlo k chybě.', $submit->getTask()->getFQName()), BasePresenter::FLASH_ERROR);
            Debugger::log($e);
        } catch (ModelException $e) {
            $this->flashMessage(sprintf('Během mazání úlohy %s došlo k chybě.', $submit->getTask()->getFQName()), BasePresenter::FLASH_ERROR);
            Debugger::log($e);
        }
    }

    /**
     * @internal
     * @param \FKSDB\ORM\Models\ModelSubmit $submit
     * @return boolean
     */
    public function canRevoke(ModelSubmit $submit) {
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            return false;
        }

        $now = time();
        $start = $submit->getTask()->submit_start ? $submit->getTask()->submit_start->getTimestamp() : 0;
        $deadline = $submit->getTask()->submit_deadline ? $submit->getTask()->submit_deadline->getTimestamp() : ($now + 1);


        return ($now <= $deadline) && ($now >= $start);
    }

}
