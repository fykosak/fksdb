<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemVariantModel;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemModel;
use FKSDB\Models\ORM\Services\Warehouse\WarehouseItemVariantService;
use FKSDB\Models\ORM\Services\Warehouse\WarehouseItemService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<warehouseItemVariantModel,array{container:array{
 *       state:string,
 *       description_cs:string,
 *       description_en:string,
 *       data:string,
 *       purchase_price:float,
 *       purchase_currency:string,
 *       placement:string,
 *       note:string,
 *      contest_id?:int,
 *  }}>
 */
final class ItemFormComponent extends ModelForm
{
    private WarehouseItemService $itemService;
    private WarehouseItemVariantService $variantService;
    private ContestModel $contest;

    public const CONTAINER = 'container';

    public function __construct(Container $container, ContestModel $contest, ?WarehouseItemVariantModel $model)
    {
        parent::__construct($container, $model);
        $this->contest = $contest;
    }

    public function injectServiceProducer(
        WarehouseItemService $itemService,
        WarehouseItemVariantService $variantService
    ): void {
        $this->itemService = $itemService;
        $this->variantService = $variantService;
    }

    protected function isSubmittedByEditAll(Form $form): bool
    {
        if (!isset($form['editAll'])) {
            return false;
        }
        if (!$form['editAll']->isSubmittedBy()) {
            return false;
        }
        return true;
    }

    protected function handleFormSuccess(Form $form): void
    {
        /** @var array $values */
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);

        if (!isset($data['contest_id'])) {
            $data['contest_id'] = $this->contest->contest_id;
        }

        if (!isset($data['item_count'])) {
            $data['item_count'] = 1;
        }

     // if editAll button was selected and the form is updating the model
        if ($this->isSubmittedByEditAll($form) && !$this->isCreating()) {
             // select all models with the same fingerprint
            $items = $this->itemService->findByFingerprint($this->model->fingerprint);
            /** @var warehouseItemVariantModel $item */
            foreach ($items as $item) {
                // save $data to separate variable
                $newModelData = $data;
                // keep item_id and note from original ItemModel
                $newModelData['item_id'] = $item->item_id;
                $newModelData['note'] = $item->note;
                $this->itemService->storeModel($newModelData, $item);
            }
        } else {
            for ($i = 0; $i < $data['item_count']; $i++) {
                $this->itemService->storeModel($data, $this->model);
            }
        }

        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Item has been updated.') : _('Item has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'warehouse_item');
        $container->addField('state', ['required' => true]);
        $container->addField('description_cs', ['required' => true]);
        $container->addField('description_en', ['required' => true]);
        $container->addField('data', ['required' => true]);
        $container->addField('purchase_price', ['required' => true]);
        $container->addField('purchase_currency', ['required' => true]);
        $container->addField('placement', ['required' => true]);
        $container->addField('note', ['required' => true]);
        $products = [];
        /** @var WarehouseItemModel $product */
        foreach ($this->itemService->getTable() as $product) {
            $products[$product->product_id] = $product->name_cs;
        }
        $container->addComponent(new SelectBox(_('Product'), $products), 'product_id', 'state');
        if ($this->isCreating()) {
            $container->addText('item_count', _('Item count'))
                ->setHtmlType('number')
                ->setDefaultValue(1)
                ->addRule(Form::MIN, _('Must be at least 1'), 1);
        }
        $form->addComponent($container, self::CONTAINER);
        if (!$this->isCreating()) {
            $form->addSubmit('editAll', _('Edit all items'))
            ->onClick[] = fn(SubmitButton $button) => $this->handleSuccess($button);
        }
    }

    protected function innerSuccess(array $values, Form $form): WarehouseItemVariantModel
    {
        $data = $values[self::CONTAINER];
        if (!isset($data['contest_id'])) {
            $data['contest_id'] = $this->contest->contest_id;
        }
        /** @var warehouseItemVariantModel $item */
        $item = $this->variantService->storeModel($data, $this->model);
        return $item;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Item has been updated.') : _('Item has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}
