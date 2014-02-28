<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\SQLConsole;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelStoredQuery;
use ModelStoredQueryParameter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory {

    public function createConsole($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $control = new SQLConsole('SQL');
        $container->addComponent($control, 'sql');

        return $container;
    }

    public function createMetadata($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name', _('Název'))
                ->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
                ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);

        $container->addText('qid', _('QID'))
                ->setOption('description', _('Dotazy s QIDem nelze smazat a QID lze použít pro práva a trvalé odkazování.'))
                ->addCondition(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 16)
                ->addRule(Form::REGEXP, _('QID může být jen z písmen anglické abecedy a číslic a tečky.'), '/^[a-z][a-z0-9.]*$/i');


        $container->addTextArea('description', _('Popis dotazu'));

        return $container;
    }

    public function createParametersMetadata($options = 0, ControlGroup $group = null) {
        $that = $this;
        $replicator = new Replicator(function($replContainer) use($that, $group) {
                    $that->buildParameterMetadata($replContainer, $group);

                    $submit = $replContainer->addSubmit('remove', _('Odebrat parametr'));
                    $submit->getControlPrototype()->addClass('btn-danger');
                    $submit->getControlPrototype()->addClass('btn-sm'); // TODO doesn't work
                    $submit->addRemoveOnClick();
                }, 0, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\ModelContainer';
        $replicator->setCurrentGroup($group);
        $submit = $replicator->addSubmit('addParam', _('Přidat parametr'));
        //$submit->getControlPrototype()->addClass('btn-default'); //TODO doesn't work
        $submit->getControlPrototype()->addClass('btn-sm'); // TODO doesn't work

        $submit->setValidationScope(false)
                ->addCreateOnClick();

        return $replicator;
    }

    /**
     * @internal
     * @param Container $container
     * @param type $group
     */
    public function buildParameterMetadata(Container $container, $group) {
        $container->setCurrentGroup($group);

        $container->addText('name', _('Název'))
                ->addRule(Form::FILLED, _('Název parametru musí být vyplněn.'))
                ->addRule(Form::MAX_LENGTH, _('Název parametru je moc dlouhý.'), 16)
                ->addRule(Form::REGEXP, _('Název parametru může být jen z malých písmen anglické abecedy, číslic nebo podtržítka.'), '/^[a-z][a-z0-9_]*$/');

        $container->addText('description', _('Popis'));

        $container->addSelect('type', _('Datový typ'))
                ->setItems(array(
                    ModelStoredQueryParameter::TYPE_INT => 'integer',
                    ModelStoredQueryParameter::TYPE_STR => 'string',
        ));

        $container->addText('default', _('Výchozí hodnota'));
    }

    public function createParametersValues(ModelStoredQuery $queryPattern, $options = 0, ControlGroup $group = null) {
        $container = new Container();
        $container->setCurrentGroup($group);

        foreach ($queryPattern->getParameters() as $parameter) {
            $name = $parameter->name;
            $subcontainer = $container->addContainer($name);

            $valueElement = $subcontainer->addText('value', $name);
            $valueElement->setOption('description', $parameter->description);
            if ($parameter->type == ModelStoredQueryParameter::TYPE_INT) {
                $valueElement->addRule(Form::INTEGER, _('Parametr %label je číselný.'));
            }

            $valueElement->setDefaultValue($parameter->getDefaultValue());
        }

        return $container;
    }

}
