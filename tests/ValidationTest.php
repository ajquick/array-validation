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

namespace Multidimensional\ArrayValidation\Test;

use Multidimensional\ArrayValidation\Exception\ValidationException;
use Multidimensional\ArrayValidation\Validation;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
     public $validation;
     
     public function setUp()
    {
        $this->validation = new Validation();
    }
    
    public function tearDown()
    {
        unset($this->validation);
    }
 
    public function testValidation()
    {
        $this->assertTrue($this->validation->isSuccess());
        $this->assertFalse($this->validation->isError());
        $this->assertNull($this->validation->getErrorMessage());
    }
    
    public function testFailure()
    {
		try {
        $this->assertFalse($this->validation->validate('', ''));
		} catch (ValidationException $e) {
        $this->assertFalse($this->validation->isSuccess());
        $this->assertTrue($this->validation->isError());
		}
		
		try {
        $this->assertFalse($this->validation->validate([], ''));
		} catch (ValidationException $e) {
        $this->assertFalse($this->validation->isSuccess());
        $this->assertTrue($this->validation->isError());
        $this->assertEquals('Validation rules array not found.', $this->validation->getErrorMessage());
		}
		
		try {
        $this->assertFalse($this->validation->validate('', []));
		} catch (ValidationException $e) {
        $this->assertFalse($this->validation->isSuccess());
        $this->assertTrue($this->validation->isError());
        $this->assertEquals('Validation array not found.', $this->validation->getErrorMessage());
		} 
    }
    
    public function testError()
    {
        try {
			/*
			Cause Error
			$this->assertFalse($this->validation->isSuccess());
			$this->assertTrue($this->validation->isError());
			//$this->assertNull($this->validation->getErrorMessage());
			*/
		} catch (ValidationException $e) {
			$this->validation->clearError();
			$this->assertTrue($this->validation->isSuccess());
			$this->assertFalse($this->validation->isError());
			$this->assertNull($this->validation->getErrorMessage());
		}
    }
    
    public function testRequiredTrue()
    {
        $rules = [
            'a' => ['type' => 'string', 'required' => true]
        ];
        $array = [
            'a' => 'Hello'
        ];
        $this->assertTrue($this->validation->validate($array, $rules));
        $this->assertTrue($this->validation->isSuccess());
    }
    
    public function testRequiredTrueFailure()
    {
        $rules = [
            'a' => ['type' => 'string', 'required' => true]
        ];
        $array = [
            'b' => 'Hello'
        ];
        $this->assertFalse($this->validation->validate($array, $rules));
        $this->assertFalse($this->validation->isSuccess());
        $this->assertTrue($this->validation->isError());
        $this->assertEquals('Required value not found for key a.', $this->validation->getErrorMessage());
        $this->validation->clearError();
        $this->assertTrue($this->validation->isSuccess());
        $this->assertFalse($this->validation->isError());
        $this->assertNull($this->validation->getErrorMessage());
    }
}