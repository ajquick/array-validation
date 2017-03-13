<?php
/**
 *      __  ___      ____  _     ___                           _                    __
 *     /  |/  /_  __/ / /_(_)___/ (_)___ ___  ___  ____  _____(_)___  ____   ____ _/ /
 *    / /|_/ / / / / / __/ / __  / / __ `__ \/ _ \/ __ \/ ___/ / __ \/ __ \ / __ `/ /
 *   / /  / / /_/ / / /_/ / /_/ / / / / / / /  __/ / / (__  ) / /_/ / / / // /_/ / /
 *  /_/  /_/\__,_/_/\__/_/\__,_/_/_/ /_/ /_/\___/_/ /_/____/_/\____/_/ /_(_)__,_/_/
 *
 *  @author Multidimension.al
 *  @copyright Copyright © 2016-2017 Multidimension.al - All Rights Reserved
 *  @license Proprietary and Confidential
 *
 *  NOTICE:  All information contained herein is, and remains the property of
 *  Multidimension.al and its suppliers, if any.  The intellectual and
 *  technical concepts contained herein are proprietary to Multidimension.al
 *  and its suppliers and may be covered by U.S. and Foreign Patents, patents in
 *  process, and are protected by trade secret or copyright law. Dissemination
 *  of this information or reproduction of this material is strictly forbidden
 *  unless prior written permission is obtained.
 */

namespace Multidimensional\ArrayValidation;

use Multidimensional\ArrayValidation\Exception\ValidationException;

class Validation
{
    /**
     * @param array $array
     * @param array $rules
     * @return true
     * @throws ValidationException
     */
    public static function validate($array, $rules)
    {
        if (is_array($array) && is_array($rules)) {
            self::required($array, $rules);
            foreach ($array as $key => $value) {
                if (!isset($rules[$key])) {
                    throw new ValidationException(sprintf("Unexpected key '%s' found in array.", $key));
                }
                self::validateField($value, $rules[$key], $key);
            }
        } elseif (!is_array($array)) {
            throw new ValidationException("Validation array not found.");
        } elseif (!is_array($rules)) {
            throw new ValidationException("Validation rules array not found.");
        }

        return true;
    }

    /**
     * @param string $value
     * @param array $rules
     * @param string $key
     * @return true
     * @throws ValidationException
     */
    public static function validateField($value, $rules, $key)
    {
        if (is_array($value) && isset($rules['fields'])) {
            return Validation::validate($value, $rules['fields']);
        } elseif (is_array($value)) {
            throw new ValidationException(sprintf("Unexpected array found for key %s.", $key));
        }

        if (isset($value) && $value !== null && $value != 'null') {
            if (isset($rules['type'])) {
                if ($rules['type'] === 'integer') {
                    self::validateInteger($value, $key);
                } elseif ($rules['type'] === 'decimal') {
                    self::validateDecimal($value, $key);
                } elseif ($rules['type'] === 'string') {
                    self::validateString($value, $key);
                } elseif ($rules['type'] === 'boolean') {
                    self::validateBoolean($value, $key);
                }
            }

            if (isset($rules['values'])) {
                self::validateValues($value, $rules['values'], $key);
            }

            if (isset($rules['pattern'])) {
                if ($rules['pattern'] == 'ISO 8601') {
                    self::validateISO8601($value, $key);
                } else {
                    self::validatePattern($value, $rules['pattern'], $key);
                }
            }
        }

        return true;
    }

    /**
     * @param array $array
     * @param array $rules
     * @return void
     */
    protected static function required($array, $rules)
    {
        if (is_array($rules)) {
            foreach ($rules as $key => $value) {
                if (isset($value['required']) && $value['required'] !== null && $value['required'] != 'null'               ) {
                    self::checkRequired($array, $value['required'], $key);
                }
            }
        }

        return;
    }

    /**
     * @param array $array
     * @param array $rules
     * @return void
     * @throws ValidationException
     */
    protected static function checkRequired($array, $required, $key)
    {
        if (is_array($required)) {
            $result = self::requiredOne($array, $required, $key);
        } else {
            $result = self::requiredTest($array, $required, $key);
        }

        if ($result && !self::requiredTrue($array, $key)) {
            throw new ValidationException(sprintf("Required value for key '%s' not found.", $key));
        }

        return;
    }

    /**
     * @param array $array
     * @param string $key
     * @return true
     * @throws ValidationException
     */
    protected static function requiredNull($array, $key, $key2)
    {
        if ((!isset($array[$key]) && !array_key_exists($key, $array))
            || ((isset($array[$key]) || array_key_exists($key, $array))
                && ($array[$key] === null || $array[$key] == 'null'))) {
            return true;
        }
        if (is_null($key2) || $key2 == 'null') {
            throw new ValidationException(sprintf("Required value not found for when key %s is NULL.", $key));
        } else {
            throw new ValidationException(sprintf("Required value for key %s not found, required when key %s is NULL.", $key2, $key));
        }
    }

    /**
     * @param array $array
     * @param string $key
     * @return true|false
     * @throws ValidationException
     */
    protected static function requiredTrue($array, $key)
    {
        if (isset($array[$key]) || array_key_exists($key, $array)) {
            return true;
        }
        //throw new ValidationException(sprintf("Required value not found for key %s.", $key));
        return false;
    }

    /**
     * @param array $array
     * @param string|array $rules
     * @param string $key
     * @return true
     * @throws ValidationException
     */
    protected static function requiredOne($array, $rules, $key)
    {
        if (is_array($rules)) {
            foreach ($rules as $rulesKey => $rulesValue) {
                if (is_array($rulesKey)) {
                    self::requiredAll($array, $rulesKey);
                } else {
                    self::requiredTest($array, $rulesValue, $rulesKey);
                }
            }
        }
        //throw new ValidationException(sprintf("Check failed to satisfy the requirements for key %s.", $key));
    }

    /**
     * @param array $array
     * @param string|array $rules
     * @return bool
     * @internal param string $key
     */
    protected static function requiredAll($array, $rules)
    {
        if (is_array($rules)) {
            foreach ($rules as $key => $value) {
                if (!self::requiredTest($array, $value, $key)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $array
     * @param $value
     * @param $key
     * @return bool
     */
    protected static function requiredTest($array, $value, $key)
    {
        if ($value === null || $value == 'null') {
            return self::requiredNull($array, $key);
        } elseif ($value === true || $value == 'true') {
            return self::requiredTrue($array, $key);
        } elseif (isset($array[$key]) && $array[$key] == $value) {
            return true;
        }

        return false;
    }

    /**
     * @param int $value
     * @param string|null $key
     * @return true|false
     * @throws ValidationException
     */
    protected static function validateInteger($value, $key)
    {
        if (!is_int($value)) {
            throw new ValidationException(sprintf("Invalid integer '%s' for key %s.", $value, $key));
        }

        return true;
    }

    /**
     * @param float       $value
     * @param string|null $key
     * @return true|false
     * @throws ValidationException
     */
    protected static function validateDecimal($value, $key)
    {
        if (!is_float($value)) {
            throw new ValidationException(sprintf("Invalid decimal '%s' for key %s.", $value, $key));
        }

        return true;
    }

    /**
     * @param string      $value
     * @param string|null $key
     * @return true|false
     * @throws ValidationException
     */
    protected static function validateString($value, $key)
    {
        if (!is_string($value)) {
            throw new ValidationException(sprintf("Invalid string '%s' for key %s.", $value, $key));
        }

        return true;
    }

    /**
     * @param bool $value
     * @param string|null $key
     * @return true
     * @throws ValidationException
     */
    protected static function validateBoolean($value, $key)
    {
        if (!is_bool($value)) {
            throw new ValidationException(sprintf("Invalid boolean '%s' for key %s.", $value, $key));
        }

        return true;
    }

    /**
     * @param string $value
     * @param string $pattern
     * @param string $key
     * @return bool
     * @throws ValidationException
     */
    protected static function validatePattern($value, $pattern, $key)
    {
        if (!preg_match('/^' . $pattern . '$/', $value)) {
            throw new ValidationException(sprintf("Invalid value '%s' does not match pattern '%s' for key %s.", $value, $pattern, $key));
        }

        return true;
    }

    /**
     * @param string $value
     * @param string $key
     * @return bool
     */
    protected static function validateISO8601($value, $key)
    {
        $pattern = '(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)';
        if (!preg_match('/^' . $pattern . '$/', $value)) {
            throw new ValidationException(sprintf("Invalid value '%s' does not match ISO 8601 pattern for key %s.", $value, $key));
        }

        return true;
    }

    /**
     * @param string $value
     * @param array|string $array
     * @param string|null $key
     * @return bool
     * @throws ValidationException
     */
    protected static function validateValues($value, $array, $key)
    {
        $compareArray = [];
        if (is_array($array)) {
            foreach ($array as $compareKey) {
                $compareScore = levenshtein($value, $compareKey);
                $compareArray[$compareKey] = $compareScore;
                if ($compareScore === 0) {
                    return true;
                }
            }
        } elseif (strcasecmp($value, $array) === 0) {
            return true;
        }

        $errorMessage = sprintf('Invalid value %s for key %s.', $value, $key);

        array_walk($compareArray, function (&$item) {
            $item = abs($item);
        });
        asort($compareArray);
        $compareArray = array_keys($compareArray);
        if (count($compareArray)) {
            $errorMessage .= sprintf(' Did you mean %s?', array_shift($compareArray));
        }

        throw new ValidationException($errorMessage);
    }
}
