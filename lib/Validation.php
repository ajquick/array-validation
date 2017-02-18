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
    }
    
    /**
     * @param array $array
     * @param array $rules
     * @return bool
     */
    public function validate($array, $rules)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (!isset($rules[$key]) || $this->validateField($value, $rules[$key], $key) !== true) {
                    return false;
                }
            }
            
            if (!$this->checkRequired($array, $rules)) {
                return false;
            }
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
        
        if (isset($rules['type'])) {
            if (($rules['type'] === 'integer' && !$this->validateInteger($value, $key))
                || ($rules['type'] === 'decimal' && !$this->validateDecimal($value, $key))
                || ($rules['type'] === 'string'  && !$this->validateString($value, $key))
                || ($rules['type'] === 'boolean' && !$this->validateBoolean($value, $key))
            ) {
                return false;
            }
        }
        
        if (isset($rules['pattern'])) {
            if ($rules['pattern'] == 'ISO 8601') {
                $rules['pattern'] = '([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?';
            }
            
            if (!preg_match('/^' . $rules['pattern'] . '$/', $value)) {
                $this->setError(sprintf("Invalid value %s does not match pattern '%s' for key %s.", $value, $rules['pattern'], $key));
                return false;
            }
        }
        
        return true;
    }
    
    /**
	 * Function returns true if all required checks pass, and false if a required check fails.
	 *
     * @param array $array
     * @param array $rules
     * @return bool
     */
    public function checkRequired($array, $rules)
    {
        if (is_array($rules)) {
            foreach ($rules as $key => $value) {
                if (isset($value['required']) && !isset($array[$key])) {
                    $failure = true;
                    if (is_array($value['required'])) {
                        foreach ($value['required'] as $key2 => $value2) {
                            if (is_array($value)) {
                                foreach ($value2 as $key3 => $value3) {
									
                                }
                            } else {
                                if ($value2 === null || $value2 == 'null') {
                                    if (!isset($array[$key2]) && $array[$key2] !== null && $array[$key2] != 'null') {
                                        $failure = false;
                                    }
                                } else {
                                    if (!isset($array[$key2]) && $array[$key2] != $value2) {
                                        $failure = false;
                                    }
                                }
                            }
                        }
                    } elseif ($value['required'] === true || $value['required'] == 'true') {
                        if (!isset($array[$key])) {
                            return false;
                        } else {
                            $failure = false;
                        }
                    }
                    if ($failure === true) {
                        return false;
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @return bool
     */
    public function isSuccess()
    {
        if ($this->error) {
            return false;
        } else {
            return true;
        }
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
    private function setError($message)
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
        $thos->errorMessage = null;
        
        return;
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
}
