<?php
/**    __  ___      ____  _     ___                           _                    __
 *    /  |/  /_  __/ / /_(_)___/ (_)___ ___  ___  ____  _____(_)___  ____   ____ _/ /
 *   / /|_/ / / / / / __/ / __  / / __ `__ \/ _ \/ __ \/ ___/ / __ \/ __ \ / __ `/ /
 *  / /  / / /_/ / / /_/ / /_/ / / / / / / /  __/ / / (__  ) / /_/ / / / // /_/ / /
 * /_/  /_/\__,_/_/\__/_/\__,_/_/_/ /_/ /_/\___/_/ /_/____/_/\____/_/ /_(_)__,_/_/
 *
 * CONFIDENTIAL
 *
 * © 2017 Multidimension.al - All Rights Reserved
 *
 * NOTICE:  All information contained herein is, and remains the property of
 * Multidimension.al and its suppliers, if any.  The intellectual and
 * technical concepts contained herein are proprietary to Multidimension.al
 * and its suppliers and may be covered by U.S. and Foreign Patents, patents in
 * process, and are protected by trade secret or copyright law. Dissemination
 * of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained.
 */

namespace Multidimensional\ArrayValidation;

class Validation
{
    public $error;
    public $errorMessage;
    
    public function __construct()
    {
        $this->error = false;
        $this->errorMessage = null;
        
        return;
    }
    
    /**
     * @param array $array
     * @param array $rules
     * @return bool
     */
    public function validate($array, $rules)
    {
        if (is_array($array) && is_array($rules)) {
            if (!$this->required($array, $rules)) {
                return false;
            }
            foreach ($array as $key => $value) {
                if (!isset($rules[$key])
                    || $this->validateField($value, $rules[$key], $key) !== true) {
                    return false;
                }
            }
        } elseif (!is_array($array)) {
            $this->setError("Validation array not found.");
            return false;
        } elseif (!is_array($rules)) {
            $this->setError("Validation rules array not found.");
            return false;
        }
 
        if ($this->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @param string $key
     * @param string $value
     * @param array  $rules
     * @return bool
     */
    public function validateField($value, $rules, $key = null)
    {
        if (is_array($value) && isset($rules['fields'])) {
            return $this->validate($value, $rules['fields']);
        } elseif (is_array($value)) {
            $this->setError(sprintf("Unexpected array found for key %s.", $key));
            return false;
        }

        if (isset($value) && $value !== null && $value != 'null') {
            if (isset($rules['type'])) {
                if (($rules['type'] === 'integer' && !$this->validateInteger($value, $key))
                    || ($rules['type'] === 'decimal' && !$this->validateDecimal($value, $key))
                    || ($rules['type'] === 'string'  && !$this->validateString($value, $key))
                    || ($rules['type'] === 'boolean' && !$this->validateBoolean($value, $key))
                ) {
                    return false;
                }
            }
    
            if (isset($rules['values'])) {
                if (!$this->validateValues($value, $rules['values'])) {
                    return false;
                }
            }
    
            if (isset($rules['pattern'])) {
                if (($rules['pattern'] == 'ISO 8601' && !$this->validateISO8601($value, $key))
                    || (!$this->validatePattern($value, $rules['pattern'], $key))) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * @param array $array
     * @param array $rules
     * @return true|false
     */
    public function required($array, $rules)
    {
        if (is_array($rules)) {
            foreach ($rules as $key => $value) {
                if (isset($value['required'])
                    && $value['required'] !== null
                    && $value['required'] != 'null') {
                    if (!isset($array[$key])
                        && !array_key_exists($key, $array)
                        && !$this->checkRequired($array, $value['required'], $key)) {
                        return false;
                    }
                }
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Function returns true if all required checks pass, and false if a required check fails.
     *
     * @param array $array
     * @param array $rules
     * @return bool
     */
    public function checkRequired($array, $required, $key)
    {
        if (is_array($required)) {
            if (!$this->requiredOne($array, $value, $key)) {
                return false;
            }
        } elseif (($required === true || $required == 'true')
            && !$this->requiredTrue($array, $key)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return bool
     */
    public function isSuccess()
    {
		return (bool) !$this->error;
	}
    
    /**
     * @return bool
     */
    public function isError()
    {
        return (bool) $this->error;
    }
    
    /**
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
    /**
     * @param string $message
     * @return void
     */
    protected function setError($message)
    {
        $this->error = true;
        $thos->errorMessage = $message;
        
        return;
    }
    
    /**
     * @return void
     */
    public function clearError()
    {
        $this->error = false;
        $this->errorMessage = null;
        
        return;
    }
    
    /**
     * @param array $array
     * @param string $key
     * @retrun true|false
     */
    protected function requiredNull($array, $key, $key2 = null)
    {
        if ((!isset($array[$key]) && !array_key_exists($key, $array))
            || ((isset($array[$key]) || array_key_exists($key, $array))
                && ($array[$key] === null || $array[$key] == 'null'))) {
            return true;
        }
        if (is_null()) {
            $this->setError(sprintf("Required value not found for when key %s is NULL.", $key));
        } else {
            $this->setError(sprintf("Required value for key $s not found, required when key %s is NULL.", $key2, $key));
        }
        
        return false;
    }

    /**
     * @param array $array
     * @param string $key
     * @retrun true|false
     */
    protected function requiredTrue($array, $key)
    {
        if (isset($array[$key]) || array_key_exists($key, $array)) {
            return true;
        }
        $this->setError(sprintf("Required value not found for key %s.", $key));
        
        return false;
    }
    
    /**
     * @param array $array
     * @param string|array $rules
     * @param string $key
     * @return bool
     */
    protected function requiredOne($array, $rules, $key = null)
    {
        if (is_array($rules)) {
            foreach ($rules as $rulesKey => $rulesValue) {
                if ((is_array($rulesKey) && $this->requiredAll($array, $rulesKey, $key))
                    || ($this->requiredTest($array, $rulesKey, $rulesValue))) {
                    return true;
                }
            }
        }
        $this->setError(sprintf("Check failed to satisfy the requirements for key %s.", $key));
     
        return false;
    }
    
    /**
     * @param array $array
     * @param string|array $rules
     * @param string $key
     * @return bool
     */
    protected function requiredAll($array, $rules, $key = null)
    {
        if (is_array($rules)) {
            foreach ($rules as $rulesKey => $rulesValue) {
                if (!$this->requiredTest($array, $rulesKey, $rulesValue)) {
                    $this->setError(sprintf("Check failed to satisfy the requirements for key %s.", $key));
                    return false;
                }
            }
        }
        
        return true;
    }
    
    protected function requiredTest($array, $key, $value)
    {
        if ($value === null || $value == 'null') {
            return $this->requiredNull($array, $key);
        } elseif ($value === true || $value == 'true') {
            return $this->requiredTrue($array, $key);
        } elseif (isset($array[$key]) && $array[$key] == $value) {
            $this->setError(sprintf("Check failed to satisfy the requirements for key %s.", $key));
            return false;
        }
        
        return true;
    }

    /**
     * @param int         $value
     * @param string|null $key
     * @return true|false
     */
    protected function validateInteger($value, $key = null)
    {
        if ($value != (int) $value) {
            if (is_null($key)) {
                      $this->setError(sprintf("Invalid integer %s != %s.", $value, (int) $value));
            } else {
                      $this->setError(sprintf("Invalid integer %s != %s for key %s.", $value, (int) $value, $key));
            }
            return false;
        }
        
        return true;
    }

    /**
     * @param float       $value
     * @param string|null $key
     * @return true|false
     */
    protected function validateDecimal($value, $key = null)
    {
        if ($value != (float) $value) {
            if (is_null($key)) {
                      $this->setError(sprintf("Invalid decimal %s != %s.", $value, (float) $value));
            } else {
                      $this->setError(sprintf("Invalid decimal %s != %s for key %s.", $value, (float) $value, $key));
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * @param string      $value
     * @param string|null $key
     * @return true|false
     */
    protected function validateString($value, $key = null)
    {
        if ($value != (string) $value) {
            if (is_null($key)) {
                      $this->setError(sprintf("Invalid string %s != %s.", $value, (string) $value));
            } else {
                      $this->setError(sprintf("Invalid string %s != %s for key %s.", $value, (string) $value, $key));
            }
            return false;
        }
        
        return true;
    }

    /**
     * @param bool        $value
     * @param string|null $key
     * @return true|false
     */
    protected function validateBoolean($value, $key = null)
    {
        if ($value != (bool) $value) {
            if (is_null($key)) {
                      $this->setError(sprintf("Invalid boolean %s != %s.", $value, (bool) $value));
            } else {
                      $this->setError(sprintf("Invalid boolean %s != %s for key %s.", $value, (bool) $value, $key));
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * @param string $value
     * @param string $pattern
     * @param string $key
     * @return bool
     */
    protected function validatePattern($value, $pattern, $key = null)
    {
        if (!preg_match('/^' . $pattern . '$/', $value)) {
            if (is_null($key)) {
                $this->setError(sprintf("Invalid value %s does not match pattern '%s' for key %s.", $value, $pattern, $key));
            } else {
                $this->setError(sprintf("Invalid value %s does not match pattern '%s'.", $value, $pattern));
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * @param string $value
     * @param string $key
     * @return bool
     */
    protected function validateISO8601($value, $key = null)
    {
        $pattern = '(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)';
        return self::validatePattern($value, $pattern, $key);
    }
    
    /**
     * @param string $value
     * @param array|string $array
     * @param string|null $key
     * @return bool
     */
    protected function validateValues($value, $array, $key = null)
    {
        $compareArray = [];
        if (is_array($array)) {
            foreach ($array as $key) {
                $compareScore = strcasecmp($value, $key);
                $compareArray[$key] = $compareScore;
                if ($compareScore === 0) {
                    return true;
                }
            }
        } elseif (strcasecmp($value, $array) === 0) {
            return true;
        }
        
        $errorMessage = '';
        
        if (is_null($key)) {
            $errorMessage = sprintf('Invalid value %s for key %s.', $value, $key);
        } else {
            $errorMessage = sprintf('Invalid value %s.', $value);
        }
        
        $compareArray = array_walk($compareArray, function(&$item) {
			$item = abs($item);
		});
        asort($compareArray);
        
        if (count($compareArray)) {
            $errorMessage .= sprintf(' Did you mean %s?', array_shift($compareArray));
        }
        
        $this->setError($errorMessage);
        
        return false;
    }
}
