<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Control\AjaxUpload\SubmitDownloadTrait;
use FKSDB\Components\Control\AjaxUpload\SubmitRevokeTrait;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SubmitsGrid extends BaseGrid {
    use SubmitRevokeTrait;
    use SubmitDownloadTrait;

    /** @var ServiceSubmit */
    private $serviceSubmit;
    /** @var CorrectedStorage */
    private $correctedStorage;
    /** @var UploadedStorage */
    private $uploadedStorage;

    /**
     * @var ModelContestant
     */
    private $contestant;

    /**
     * SubmitsGrid constructor.
     * @param Container $container
     * @param ModelContestant $contestant
     */
    public function __construct(Container $container, ModelContestant $contestant) {
        parent::__construct($container);
        $this->contestant = $contestant;
    }

    /**
     * @param ServiceSubmit $serviceSubmit
     * @param CorrectedStorage $correctedStorage
     * @param UploadedStorage $uploadedStorage
     * @return void
     */
    public function injectPrimary(ServiceSubmit $serviceSubmit, CorrectedStorage $correctedStorage, UploadedStorage $uploadedStorage) {
        $this->serviceSubmit = $serviceSubmit;
        $this->correctedStorage = $correctedStorage;
        $this->uploadedStorage = $uploadedStorage;
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
        $submits = $this->serviceSubmit->getSubmits();
        $submits->where('ct_id = ?', $this->contestant->ct_id); //TODO year + contest?

        $this->setDataSource(new NDataSource($submits));
        $this->setDefaultOrder('series DESC, tasknr ASC');

        //
        // columns
        //
        $this->addColumn('task', _('Task'))
            ->setRenderer(function (ModelSubmit $row) use ($presenter) {
                return $row->getTask()->getFQName();
            });
        $this->addColumn('submitted_on', _('Čas odevzdání'));
        $this->addColumn('source', _('Způsob odevzdání'));

        //
        // operations
        //
        $this->addButton('revoke', _('Cancel'))
            ->setClass('btn btn-sm btn-warning')
            ->setText(_('Cancel'))
            ->setShow(function (ModelSubmit $row) {
                return $this->canRevoke($row);
            })
            ->setLink(function (ModelSubmit $row) {
                return $this->link('revoke!', $row->submit_id);
            })
            ->setConfirmationDialog(function (ModelSubmit $row) {
                return \sprintf(_('Opravdu vzít řešení úlohy %s zpět?'), $row->getTask()->getFQName());
            });
        $this->addButton('download_uploaded')
            ->setText(_('Download original'))->setLink(function (ModelSubmit $row) {
                return $this->link('downloadUploaded!', $row->submit_id);
            });
        $this->addButton('download_corrected')
            ->setText(_('Download corrected'))->setLink(function (ModelSubmit $row) {
                return $this->link('downloadCorrected!', $row->submit_id);
            })->setShow(function (ModelSubmit $row) {
                return $row->corrected;
            });

        $this->paginate = false;
        $this->enableSorting = false;
    }

    /**
     * @param $id
     * @throws InvalidLinkException
     */
    public function handleRevoke(int $id) {
        $logger = new MemoryLogger();
        $this->traitHandleRevoke($logger, $id);
        FlashMessageDump::dump($logger, $this);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id) {
        $logger = new MemoryLogger();
        $this->traitHandleDownloadUploaded($logger, $id);
        FlashMessageDump::dump($logger, $this);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id) {
        $logger = new MemoryLogger();
        $this->traitHandleDownloadCorrected($logger, $id);
        FlashMessageDump::dump($logger, $this);
    }

    protected function getCorrectedStorage(): CorrectedStorage {
        return $this->correctedStorage;
    }

    protected function getUploadedStorage(): UploadedStorage {
        return $this->uploadedStorage;
    }

    protected function getServiceSubmit(): ServiceSubmit {
        return $this->serviceSubmit;
    }
}
