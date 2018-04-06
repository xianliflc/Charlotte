<?php
// TODO: more comments and documentation for this DataTypes
namespace Charlotte\ORM;

class DBTypes {

    // mappings for datatypes in database to those in PHP
    public const TYPE_INTEGER = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_BINARY = 'string';
    public const TYPE_JSON = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_BLOB = 'string';
    public const TYPE_BIT = 'integer';
    public const TYPE_FLOAT = 'double';
    public const TYPE_DATE = 'string';
    public const TYPE_ENUM = 'array';
    public const TYPE_SET = 'array';


    /**
     * get the parsed properties from the original property list
     * @param array $properties
     * @return array
     */
    public static function getDataTypes(array $properties) {
        $result = array();
        // TODO: db types: update the logic beased on db driver
        foreach($properties as $key => $value) {
            $type = '';
            if (strpos($value['Type'], 'bit') !== false) {
                $type = self::TYPE_BIT;
            } elseif (strpos($value['Type'], 'int') !== false) {
                $type = self::TYPE_INTEGER;
            } elseif (strpos($value['Type'], 'binary') !== false) {
                $type = self::TYPE_BINARY;
            } elseif (strpos($value['Type'], 'varchar') !== false || strpos($value['Type'], 'text') !== false ||
                        strpos($value['Type'], 'char') !== false || strpos($value['Type'], 'blob') !== false) {
                $type = self::TYPE_STRING;
            } elseif (strpos($value['Type'], 'dec') !== false || strpos($value['Type'], 'float') !== false 
                        || strpos($value['Type'], 'double') !== false || strpos($value['Type'], 'numeric' )
                        || strpos($value['Type'], 'fixed' ) !== false || strpos($value['Type'], 'long')) {
                $type = self::TYPE_FLOAT;
            } elseif (strpos($value['Type'], 'date') !== false || strpos($value['Type'], 'time') !== false 
                        || strpos($value['Type'], 'year') !== false) {
                $type = self::TYPE_DATE;
            } elseif (strpos($value['Type'], 'enum') !== false) {
                $type = self::TYPE_ENUM;
            } elseif (strpos($value['Type'], 'set') !== false) {
                $type = self::TYPE_SET;
            } elseif (strpos($value['Type'], 'bool') !== false || strpos($value['Type'], 'boolean') !== false) {
                $type = self::TYPE_BOOLEAN;
            } else {
                $type = self::TYPE_STRING;
            }
            $result[$key] = $type;
        }
        return $result;
    }

    /**
     * get the parsed not null properties from original property list
     * @param array $properties
     * @return array
     */
    public static function getNotNullValues(array $properties) {
        $result = array();
        foreach($properties as $key => $value) {
            if ($value['Null'] === 'NO' && $value['Default'] === null && $value['Extra'] === '') {
                $result[] = $key;
            }
        }
        return $result;        
    }

    /**
     * Get parsed primary keys from original property list
     * @param array $properties
     * @return array
     */
    public static function getPrimaryKeys(array $properties) {
        $result = array();
        foreach($properties as $key => $value) {
            if ($value['Key'] === 'PRI') {
                $result[] = $key;
            }
        }
        return $result;
    }

    /**
     * @param array $keys
     * @param array $properties
     * @return array
     */
    public static function getMandatoryPrimaryKeys(array $keys, array $properties) {
        foreach ($keys as $k => $key) {
            if($properties[$key]['Extra'] != '') {
                unset($keys[$k]);
            }
        }

        return $keys;
    }
}