<?php
/**
 *       __  ___      ____  _     ___                           _                    __
 *      /  |/  /_  __/ / /_(_)___/ (_)___ ___  ___  ____  _____(_)___  ____   ____ _/ /
 *     / /|_/ / / / / / __/ / __  / / __ `__ \/ _ \/ __ \/ ___/ / __ \/ __ \ / __ `/ /
 *    / /  / / /_/ / / /_/ / /_/ / / / / / / /  __/ / / (__  ) / /_/ / / / // /_/ / /
 *   /_/  /_/\__,_/_/\__/_/\__,_/_/_/ /_/ /_/\___/_/ /_/____/_/\____/_/ /_(_)__,_/_/
 *
 *  Array Validation Library
 *  Copyright (c) Multidimension.al (http://multidimension.al)
 *  Github : https://github.com/multidimension-al/array-validation
 *
 *  Licensed under The MIT License
 *  For full copyright and license information, please see the LICENSE file
 *  Redistributions of files must retain the above copyright notice.
 *
 *  @copyright  Copyright © 2017-2019 Multidimension.al (http://multidimension.al)
 *  @link       https://github.com/multidimension-al/array-validation Github
 *  @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Multidimensional\ArrayValidation\Test;

use Exception;
use Multidimensional\ArrayValidation\Validation;
use PHPUnit\Framework\TestCase;

class PatternTest extends TestCase
{

    public function testPatternURL()
    {
        $rules = [
            'a' => ['type' => 'string', 'pattern' => 'URL'],
        ];
        $array = [
            'a' => 'http://www.google.com/',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => '<a href="http://www.google.com/">Google</a>',
        ];

        try {
            $this->assertFalse(Validation::validate($array, $rules));
        } catch (Exception $e) {
            $this->assertEquals('Invalid value "<a href="http://www.google.com/">Google</a>" does not match URL pattern for key: a.', $e->getMessage());
        }
    }

    public function testPatternEmail()
    {
        $rules = [
            'a' => ['type' => 'string', 'pattern' => 'EMAIL'],
        ];
        $array = [
            'a' => 'noreply@domain.com',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => 'noreply AT domain.com',
        ];

        try {
            $this->assertFalse(Validation::validate($array, $rules));
        } catch (Exception $e) {
            $this->assertEquals('Invalid value "noreply AT domain.com" does not match email pattern for key: a.', $e->getMessage());
        }
    }

    public function testPatternMAC()
    {
        $rules = [
            'a' => ['type' => 'string', 'pattern' => 'MAC'],
        ];
        $array = [
            'a' => 'AB:CD:EF:12:34:56',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => 'AB-CD-EF-12-34-56',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => '127.0.0.1',
        ];

        try {
            $this->assertFalse(Validation::validate($array, $rules));
        } catch (Exception $e) {
            $this->assertEquals('Invalid value "127.0.0.1" does not match MAC address pattern for key: a.', $e->getMessage());
        }
    }

    public function testPatternIP()
    {
        $rules = [
            'a' => ['type' => 'string', 'pattern' => 'IP'],
        ];
        $array = [
            'a' => '127.0.0.1',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => '255.255.255.255',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => '0.0.0.0',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ];
        $this->assertTrue(Validation::validate($array, $rules));

        $array = [
            'a' => '127/0/0/1',
        ];

        try {
            $this->assertFalse(Validation::validate($array, $rules));
        } catch (Exception $e) {
            $this->assertEquals('Invalid value "127/0/0/1" does not match IP address pattern for key: a.', $e->getMessage());
        }
    }

}