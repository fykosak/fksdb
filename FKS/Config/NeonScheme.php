<?php

namespace FKS\Config;

use FKS\Config\Expressions\Helpers;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;

/**
 * So far only helper methods to "checked" laoding of Neon configuration.
 * The scheme (metamodel) for the configuration is Neon-encoded as well.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class NeonScheme {

    const TYPE_NEON = 'neon';
    const TYPE_EXPRESSION = 'expression';

    public static function readSection($section, $sectionScheme) {
        $result = array();
        foreach ($sectionScheme as $key => $metadata) {
            if ($metadata === null || !array_key_exists('default', $metadata)) {
                try {
                    $result[$key] = Arrays::get($section, $key);
                } catch (InvalidArgumentException $e) {
                    throw new NeonSchemaException("Expected key '$key' not found.", null, $e);
                }
                if ($metadata === null) {
                    continue;
                }
            } else {
                $result[$key] = Arrays::get($section, $key, $metadata['default']);
            }

            $type = Arrays::get($metadata, 'type', self::TYPE_NEON);
            if ($type == self::TYPE_EXPRESSION) {
                $result[$key] = Helpers::statementFromExpression($result[$key]);
            }
        }
        $unknown = array_diff(array_keys($section), array_keys($sectionScheme));
        if ($unknown) {
            throw new NeonSchemaException('Unknown key(s): ' . implode(', ', $unknown) . '.');
        }
        return $result;
    }

}

class NeonSchemaException extends InvalidStateException {
    
}

