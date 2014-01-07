<?php

namespace Events;

use FKS\Config\Functional\Helpers;
use Nette\Config\CompilerExtension;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nette\Utils\PhpGenerator\ClassType;
use Nette\Utils\PhpGenerator\Method;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class MachineExtension extends CompilerExtension {

    const MAIN_FACTORY = 'eventMachine';
    const MACHINE_PREFIX = 'EventMachine_';
    const BASE_MACHINE_PREFIX = 'BaseMachine_';

    private $definitions = array();
    private $idMaps = array();

    public function loadConfiguration() {
        parent::loadConfiguration();

        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();

        foreach ($config as $definitionName => $definition) {
            $this->definitions[$definitionName] = $definition;
            $eventTypeId = $definition['event_type_id'];
            $years = isset($definition['eventYears']) ? $definition['eventYears'] : true;

            if (!isset($this->idMaps[$eventTypeId])) {
                $this->idMaps[$eventTypeId] = array();
            }
            $this->idMaps[$eventTypeId][] = array(
                'name' => $definitionName,
                'years' => $years,
            );

            $this->createMachineFactory($definitionName, $definition);
        }

        $def = $builder->addDefinition(self::MAIN_FACTORY);
        $def->setShared(false);
        $def->setClass('Events\Machine');
        $def->setParameters(array('eventType', 'eventYear'));
    }

    public function afterCompile(ClassType $class) {
        $methodName = Container::getMethodName(self::MAIN_FACTORY, false);
        $method = $class->methods[$methodName];
        $this->createDispatchFactory($method);
    }

    private function createDispatchFactory(Method $method) {
        $method->setBody(NULL);
        $method->addBody('$eventTypeId = $eventType->getPrimary();');
        $method->addBody('switch($eventTypeId) {');
        foreach ($this->idMaps as $eventTypeId => $definitions) {
            $method->addBody('case ?:', array($eventTypeId));
            $universalDefinition = null;
            $indent = "\t";
            foreach ($definitions as $definition) {
                $machineName = $this->getMachineName($definition['name']);
                $methodName = Container::getMethodName($machineName, false);
                $body = "return \$this->$methodName();";
                if ($definition['years'] === true) {
                    $universalDefinition = $body;
                } else {
                    $method->addBody($indent . 'if(in_array($eventYear, ?)) ' . $body, array($definition['years']));
                }
            }
            if ($universalDefinition) {
                $method->addBody($indent . $universalDefinition);
            } else {
                $method->addBody($indent . 'throw new Nette\InvalidArgumentException("Undefined year $eventYear for event_type_id $eventTypeId");');
            }
            $method->addBody('break;');
        }
        $method->addBody('default:
                throw new Nette\InvalidArgumentException("Unknown event_type_id $eventTypeId");
            break;
        }');
    }

    private function createMachineFactory($name, $definition) {
        // create base machines factories
        foreach ($definition['baseMachines'] as $baseMachineName => $baseMachineDef) {
            $this->createBaseMachineFactory($name, $baseMachineName, $baseMachineDef);
        }

        // create factory definition
        $factoryName = $this->getMachineName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass('Events\Machine');
        $factory->setInternal(true);

        // use base machine factories to build the machine
        $primaryName = null;
        foreach ($definition['machine']['baseMachines'] as $instanceName => $instanceDef) {
            $baseName = $instanceDef['machine'];
            $required = Arrays::get($instanceDef, 'required', false);
            $primary = Arrays::get($instanceDef, 'primary', false);
            if ($primary) {
                if (!$primaryName) {
                    $primaryName = $instanceName;
                } else {
                    throw new MachineDefinitionException('Multiple primary machines defined.');
                }
            }

            $baseMachineName = $this->getBaseMachineName($name, $baseName);
            $methodName = Container::getMethodName($baseMachineName, false);
            $factory->addSetup("\$service->addBaseMachine(\$this->$methodName(?, ?))", array($instanceName, $required));
        }
        if (!$primaryName) {
            throw new MachineDefinitionException('No primary machine defined.');
        }
        $factory->addSetup('setPrimaryMachine', $primaryName);

        // joins (only after the machine is assembled)
        foreach ($definition['machine']['baseMachines'] as $instanceName => $instanceDef) {
            $joins = Arrays::get($definition['machine']['joins'], $instanceName, array());

            foreach ($joins as $mask => $induced) {
                $factory->addSetup("\$service->getBaseMachine(?)->addInducedTransition(?, ?)", array($instanceName, $mask, $induced));
            }
        }

        $onSubmit = Arrays::get($definition['machine'], 'onSubmit', array());
        $factory->addSetup('$service->onSubmit[] = ?', $onSubmit);

        $handler = Arrays::get($definition['machine'], 'handler', null); //TODO some default handler?
        $factory->addSetup('setHandler', $handler);
        $factory->addSetup('freeze');
    }

    private function createBaseMachineFactory($name, $baseName, $definition) {
        $factoryName = $this->getBaseMachineName($name, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass('Events\BaseMachine');
        $factory->setInternal(true);
        $factory->setParameters(array('instanceName', 'required'));

        foreach ($definition['states'] as $state => $label) {
            $factory->addSetup('addState', $state, $label);
        }

        foreach ($definition['transitions'] as $mask => $transitionDef) {
            $label = Arrays::get($transitionDef, 'label', $mask);
            $condition = Arrays::get($transitionDef, 'condition', true);
            $after = Arrays::get($transitionDef, 'after', null);

            $factory->addSetup('addTransition', array(
                $mask,
                $label,
                Helpers::createConditionStatement($condition),
                $after
            ));
        }
    }

    private function getMachineName($name) {
        return self::MACHINE_PREFIX . $name;
    }

    private function getBaseMachineName($name, $baseName) {
        return self::BASE_MACHINE_PREFIX . $name . '_' . $baseName;
    }

}

class MachineDefinitionException extends InvalidStateException {
    
}

