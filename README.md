# Array Validation Library

[![Build Status](https://travis-ci.org/multidimension-al/array-validation.svg)](https://travis-ci.org/multidimension-al/array-validation)
[![Latest Stable Version](https://poser.pugx.org/multidimensional/array-validation/v/stable.svg)](https://packagist.org/packages/multidimensional/array-validation)
[![Code Coverage](https://scrutinizer-ci.com/g/multidimension-al/array-validation/badges/coverage.png)](https://scrutinizer-ci.com/g/multidimension-al/array-validation/)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/multidimensional/array-validation/license.svg)](https://packagist.org/packages/multidimensional/array-validation)
[![Total Downloads](https://poser.pugx.org/multidimensional/array-validation/d/total.svg)](https://packagist.org/packages/multidimensional/array-validation)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/multidimension-al/array-validation/badges/quality-score.png)](https://scrutinizer-ci.com/g/multidimension-al/array-validation/)

This library allows for validation of an array using another array as a ruleset. Validation rules include requiring specific keys, specific types and specific values. When the validation fails, the library throws a normal _Exception_. For best results, also consider using our [Array Sanitization](https://github.com/multidimension-al/array-sanitization) library which will correct errors and force type corrections prior to validation.

## Requirements

* PHP 5.5+

# Installation

The easiest way to install this library is to use composer. To install, simply include the following in your ```composer.json``` file:

```
"require": {
    "multidimensional/array-validation": "*"
}
```

Or run the following command from a terminal or shell:

```
composer require --prefer-dist multidimensional/array-validation
```

You can also specify version constraints, with more information available [here](https://getcomposer.org/doc/articles/versions.md).

# Usage

This library utilizes PSR-4 autoloading, so make sure you include the library near the top of your class file:

```php
use Multidimensional\ArrayValidation\Validation;
```

How to use in your code:

__Create Ruleset__

```php
$rules = ['keyName' => ['type' => 'string', 'required' => true]];
```

__Check an array against that ruleset__

```php
$array = ['keyName' => 'text'];
$bool = Validation::validate($array, $rules);
echo $bool; //true
```

The validation process will return true or false and will throw an exception.

# Advanced Rulesets

Many examples of rules can be found in the ```ValidationTest.php``` file.

### Types

There are several different valid types for the values in the array they are as follows:

* String
* Integer
* Decimal
* Boolean
* Group (Multidimensional array)

### Pattern Matching

You can specify basic regular expressions that can be used to access whether a value is valid or not.

Examples of pattern matching:

* [A-Z]{2} _(match two capital letters)_
* \d{5} _(matches 5 digits)_
* \d{5}-\d{4} _(matches ZIP+4)_

Built in patterns available for matching:

* URL
* Email
* IP Address (IPv4 or IPv6)
* MAC Address (Dashes, Colons or Dots) 
* [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601)

### Required Values

It is possible to set a key as being required or not, but it is also possible to adjust requirements based on the the values of the other keys. For example, a key could be required if the value of another key is equal to _null_ or if another key has a specific value.

Here is an example from our _ValidationTest.php_ file:

```php
$rules = [
  'a' => ['type' => 'string', 'required' => true],
  'b' => ['type' => 'string',
    'required' => [
	  'a' => 'banana'
	 ]
  ],
  'c' => ['type' => 'string',
    'required' => [
	  [
	    'a' => 'banana',
		'b' => 'orange'
	  ],
	  [
		'a' => 'banana',
		'b' => 'carrot'
	  ],
	  [
		'a' => 'pickle'
	  ],
	  [
		'b' => 'banana'
	  ]
	]
  ]
];
```

Under this example the following are true:

* Key 'a' is always required.
* Key 'b' is required when key 'a' is "banana".
* Key 'c' is required when ONE of the following is true:
  * Key 'a' is "banana" and key 'b' is "orange"
  * Key 'a' is "banana" and key 'b' is "carrot"
  * Key 'a' is "pickle"
  * Key 'b' is "banana"  

Here you can see the required functionality can be made to be quite complex.

### Specified Values

In some instances where you would only have a small subset of specific values that would be considered valid, you can specify those values in the ruleset by providing am array of valid values.

```php
$rules = ['a' => ['type' => 'string', 'values' => ['cat', 'dog']]];
```

In the above example, key 'a' must be either "cat" or "dog". Note that this string comparison is case-insensitive!


# Contributing

We appreciate all help in improving this library by either adding functions or improving existing functionality. If you do want to add to our library, please ensure you use PSR-2 formatting and add unit testing for all added functions.

Feel free to fork and submit a pull request!

# License

    MIT License
    
    Copyright (c) 2017 - 2019 multidimension.al
    
    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:
    
    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.