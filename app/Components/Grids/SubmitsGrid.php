<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\ModelException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\StorageException;
use FKSDB\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use Tracy\Debugger;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SubmitsGrid extends BaseGrid {

    private ServiceSubmit $serviceSubmit;

    private ModelContestant $contestant;

    private SubmitHandlerFactory $submitHandlerFactory;

    private int $academicYear;

    /**
     * SubmitsGrid constructor.
     * @param Container $container
     * @param ModelContestant $contestant
     * @param int $academicYear
     */
    public function __construct(Container $container, ModelContestant $contestant, int $academicYear) {
        parent::__construct($container);
        $this->contestant = $contestant;
        $this->academicYear = $academicYear;
    }

    public function injectPrimary(ServiceSubmit $serviceSubmit, SubmitHandlerFactory $submitHandlerFactory): void {
        $this->serviceSubmit = $serviceSubmit;
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    protected function getData(): IDataSource {
        $submits = $this->serviceSubmit->getSubmits();
        $submits->where('ct_id = ?', $this->contestant->ct_id); //TODO year + contest?
        return new NDataSource($submits);
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->setDefaultOrder('series DESC, tasknr ASC');

        //
        // columns
        //
        $this->addColumn('task', _('Task'))
            ->setRenderer(function (ModelSubmit $row): string {
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
            ->setShow(function (ModelSubmit $row): bool {
                return $row->canRevoke();
            })
            ->setLink(function (ModelSubmit $row): string {
                return $this->link('revoke!', $row->submit_id);
            })
            ->setConfirmationDialog(function (ModelSubmit $row): string {
                return \sprintf(_('Opravdu vzít řešení úlohy %s zpět?'), $row->getTask()->getFQName());
            });
        $this->addButton('download_uploaded')
            ->setText(_('Download original'))->setLink(function (ModelSubmit $row): string {
                return $this->link('downloadUploaded!', $row->submit_id);
            })
            ->setShow(function (ModelSubmit $row): bool {
                return !$row->isQuiz();
            });
        $this->addButton('download_corrected')
            ->setText(_('Download corrected'))->setLink(function (ModelSubmit $row): string {
                return $this->link('downloadCorrected!', $row->submit_id);
            })->setShow(function (ModelSubmit $row): bool {
                if (!$row->isQuiz()){
                    return $row->corrected;
                } else {
                    return false;
                }
            });

        $this->paginate = false;
        $this->enableSorting = false;
    }

    /**
     * @param int $id
     * @return void
     * @throws InvalidLinkException
     */
    public function handleRevoke(int $id) {
        $logger = new MemoryLogger();
        try {
            $this->submitHandlerFactory->handleRevoke($this->getPresenter(), $logger, $id);
            FlashMessageDump::dump($logger, $this);
        } catch (ForbiddenRequestException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (NotFoundException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (StorageException$exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Během mazání úlohy %s došlo k chybě.'), Message::LVL_DANGER);
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Během mazání úlohy %s došlo k chybě.'), Message::LVL_DANGER);
        }
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id) {
        $logger = new MemoryLogger();
        try {
            $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $logger, $id);
            FlashMessageDump::dump($logger, $this);
        } catch (ForbiddenRequestException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (NotFoundException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (StorageException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        }
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id) {
        $logger = new MemoryLogger();
        try {
            $this->submitHandlerFactory->handleDownloadCorrected($this->getPresenter(), $logger, $id);
            FlashMessageDump::dump($logger, $this);
        } catch (ForbiddenRequestException$exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (NotFoundException$exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (StorageException $exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        }
    }
}
