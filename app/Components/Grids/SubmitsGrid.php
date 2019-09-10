<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Control\AjaxUpload\SubmitRevokeTrait;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemSubmitStorage;
use FKSDB\Submits\ISubmitStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use function sprintf;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SubmitsGrid extends BaseGrid {
    use SubmitRevokeTrait;

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
     * @return ServiceSubmit
     */
    protected function getServiceSubmit(): ServiceSubmit {
        return $this->submitService;
    }

    /**
     * @return ISubmitStorage
     */
    protected function getSubmitStorage(): ISubmitStorage {
        return $this->submitStorage;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
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
            ->setRenderer(function ($row) use ($presenter) {
                $row->task_id; // stupid caching...
                $task = $row->getTask();
                $FQname = $task->getFQName();

                if ($row->source == ModelSubmit::SOURCE_UPLOAD) {
                    $el = Html::el('a');
                    $el->href = $presenter->link(':Public:Submit:download', ['id' => $row->submit_id]);
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
            ->setShow(function ($row) {
                return $this->canRevoke($row);
            })
            ->setLink(function ($row) {
                return $this->link('revoke!', $row->submit_id);
            })
            ->setConfirmationDialog(function ($row) {
                return sprintf(_('Opravdu vzít řešení úlohy %s zpět?'), $row->getTask()->getFQName());
            });
        $this->paginate = false;
        $this->enableSorting = false;
    }

    /**
     * @param $id
     * @throws InvalidLinkException
     */
    public function handleRevoke($id) {
        /**
         * @var Message $message
         */
        list($message,) = $this->traitHandleRevoke($id);
        $this->flashMessage($message->getMessage(), $message->getLevel());
    }
}
