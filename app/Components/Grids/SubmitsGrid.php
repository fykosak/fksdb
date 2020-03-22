<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Control\AjaxUpload\SubmitDownloadTrait;
use FKSDB\Components\Control\AjaxUpload\SubmitRevokeTrait;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemCorrectedSubmitStorage;
use FKSDB\Submits\FilesystemUploadedSubmitStorage;
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
    private $submitService;

    /** @var FilesystemUploadedSubmitStorage */
    private $filesystemSubmitUploadedStorage;

    /**
     * @var ModelContestant
     */
    private $contestant;
    /**
     * @var FilesystemCorrectedSubmitStorage
     */
    private $filesystemCorrectedSubmitStorage;

    /**
     * SubmitsGrid constructor.
     * @param Container $container
     * @param ModelContestant $contestant
     */
    function __construct(Container $container, ModelContestant $contestant) {
        parent::__construct($container);
        $this->filesystemCorrectedSubmitStorage = $container->getByType(FilesystemCorrectedSubmitStorage::class);
        $this->submitService = $container->getByType(ServiceSubmit::class);
        $this->filesystemSubmitUploadedStorage = $container->getByType(FilesystemUploadedSubmitStorage::class);
        $this->contestant = $contestant;
    }

    /**
     * @return ServiceSubmit
     */
    protected function getServiceSubmit(): ServiceSubmit {
        return $this->submitService;
    }

    /**
     * @return FilesystemUploadedSubmitStorage
     */
    protected function getSubmitUploadedStorage(): FilesystemUploadedSubmitStorage {
        return $this->filesystemSubmitUploadedStorage;
    }

    /**
     * @inheritDoc
     */
    protected function getSubmitCorrectedStorage(): FilesystemCorrectedSubmitStorage {
        return $this->filesystemCorrectedSubmitStorage;
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
            ->setRenderer(function (ModelSubmit $row) use ($presenter) {
                return $row->getTask()->getFQName();
            });
        $this->addColumn('submitted_on', _('Čas odevzdání'));
        $this->addColumn('source', _('Způsob odevzdání'));

        //
        // operations
        //
        $this->addButton('revoke', _('Zrušit'))
            ->setClass('btn btn-sm btn-warning')
            ->setText(_('Zrušit'))
            ->setShow(function ($row) {
                return $this->canRevoke($row);
            })
            ->setLink(function ($row) {
                return $this->link('revoke!', $row->submit_id);
            })
            ->setConfirmationDialog(function (ModelSubmit $row) {
                return \sprintf(_('Opravdu vzít řešení úlohy %s zpět?'), $row->getTask()->getFQName());
            });
        $this->addButton('download_uploaded')
            ->setText(_('Download original'))->setLink(function ($row) {
                return $this->link('downloadUploaded!', $row->submit_id);
            });
        $this->addButton('download_corrected')
            ->setText(_('Download corrected'))->setLink(function ($row) {
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
        list($message,) = $this->traitHandleRevoke($id);
        $this->flashMessage($message->getMessage(), $message->getLevel());
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id) {
        list($message) = $this->traitHandleDownloadUploaded($id);
        $this->flashMessage($message->getMessage(), $message->getLevel());
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id) {
        list($message) = $this->traitHandleDownloadCorrected($id);
        $this->flashMessage($message->getMessage(), $message->getLevel());
    }
}
