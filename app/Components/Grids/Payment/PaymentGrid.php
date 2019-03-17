<?php


namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use Nette\Utils\Html;

/**
 * Class PaymentGrid
 * @package FKSDB\Components\Grids\Payment
 */
abstract class PaymentGrid extends BaseGrid {
    /**
     * @var ServicePayment
     */
    protected $servicePayment;

    /**
     * PaymentGrid constructor.
     * @param ServicePayment $servicePayment
     */
    function __construct(ServicePayment $servicePayment) {
        parent::__construct();
        $this->servicePayment = $servicePayment;
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnPaymentId() {
        $this->addColumn('id', _('#'))->setRenderer(function ($row) {
            return '#' . ModelPayment::createFromTableRow($row)->getPaymentId();
        });
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnPrice() {
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            $model = ModelPayment::createFromTableRow($row);
            return $model->getPrice()->__toString();
        });
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnState() {
        $this->addColumn('state', _('Status'))->setRenderer(function ($row) {
            $model = ModelPayment::createFromTableRow($row);
            return Html::el('span')->addAttributes(['class' => $model->getUIClass()])->add(_($model->getStateLabel()));
        });
    }

    /**
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function addButtonDetail() {
        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link(':Event:payment:detail', [
                    'id' => $row->payment_id,
                    'eventId' => $row->event_id,
                ]);
            });
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnsSymbols() {
        //$this->addColumn('constant_symbol', _('CS'));
        $this->addColumn('variable_symbol', _('VS'));
        // $this->addColumn('specific_symbol', _('SS'));
        // $this->addColumn('bank_account', _('Bank acc.'));
    }

}
