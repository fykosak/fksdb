<?php

namespace FKSDB\Config;

use FKSDB\Config\Expressions\Helpers;
use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;
use Nette\Utils\NeonException;

/**
 * So far only helper methods to "checked" laoding of Neon configuration.
 * The scheme (metamodel) for the configuration is Neon-encoded as well.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class NeonScheme {

    const TYPE_NEON = 'neon';
    const TYPE_EXPRESSION = 'expression';
    const QUALIFIER_ARRAY = 'array';

    public static function readSection($section, $sectionScheme) {
        if (!is_array($section)) {
            throw new NeonSchemaException('Expected array got \'' . (string)$section . '\'.');
        }
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

            $typeDef = Arrays::get($metadata, 'type', self::TYPE_NEON);
            $typeDef = explode(' ', $typeDef);
            $type = $typeDef[0];
            $qualifier = Arrays::get($typeDef, 1, null);

            if ($type == self::TYPE_EXPRESSION) {
                if ($qualifier == self::QUALIFIER_ARRAY) {
                    $result[$key] = array_map(function ($it) {
                        return Helpers::statementFromExpression($it);
                    }, $result[$key]);
                } else if ($qualifier === null) {
                    $result[$key] = Helpers::statementFromExpression($result[$key]);
                } else {
                    throw new NeonSchemaException("Unknown type qualifier '$qualifier'.");
                }
            } else if ($type != self::TYPE_NEON) {
                throw new NeonSchemaException("Unknown type '$type'.");
            }
        }
        $unknown = array_diff(array_keys($section), array_keys($sectionScheme));
        if ($unknown) {
            throw new NeonSchemaException('Unknown key(s): ' . implode(', ', $unknown) . '.');
        }
        return $result;
    }

}

class NeonSchemaException extends NeonException {

}

