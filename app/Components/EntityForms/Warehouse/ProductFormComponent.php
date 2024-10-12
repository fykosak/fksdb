<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemModel;
use FKSDB\Models\ORM\Services\Warehouse\WarehouseItemService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<WarehouseItemModel,array{container:array{
 *        name_cs:string,
 *        name_en:string,
 *        description_cs:string,
 *        description_en:string,
 *        category:string,
 *        note:string,
 *  }}>
 */
final class ProductFormComponent extends ModelForm
{
    private WarehouseItemService $itemService;

    public const CONTAINER = 'container';

    public function injectServiceProducer(WarehouseItemService $itemService): void
    {
        $this->itemService = $itemService;
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
        $container = new ModelContainer($this->container, 'warehouse_product');
        $container->addField('name_cs', ['required' => true]);
        $container->addField('name_en', ['required' => true]);
        $container->addField('description_cs', ['required' => true]);
        $container->addField('description_en', ['required' => true]);
        $container->addField('category', ['required' => true]);
        $container->addField('note', ['required' => true]);
        $container->addText('url', _('URL'))->addRule(Form::URL);
        $form->addComponent($container, self::CONTAINER);
    }

    protected function innerSuccess(array $values, Form $form): WarehouseItemModel
    {
        /** @var WarehouseItemModel $product */
        $product = $this->itemService->storeModel($values[self::CONTAINER], $this->model);
        return $product;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Product has been updated.') : _('Product has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}
