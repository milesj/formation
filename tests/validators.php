<?php
/**
 * Formation
 *
 * A class that builds form elements and then validates their input values for errors.
 * Once validated, a cleaned data result is returned, or an array of errors are returned.
 * Furthermore, the system is split into 2 classes, Form which deals with building,
 * and Formation that contains static methods for validation.
 *
 * @author      Miles Johnson - http://milesj.me
 * @copyright   Copyright 2006-2011, Miles Johnson, Inc.
 * @license     http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link        http://milesj.me/code/php/formation
 */

// Turn on error reporting
error_reporting(E_ALL);

function debug($method, $result) {
	echo '<pre>'. $method .': '. (($result || $result === '0') ? 'pass' : 'fail') .'</pre>';
}

// Include and initialize
include_once '../formation/Formation.php';

// Check length
debug("checkLength(27, 30, 1)", Formation::checkLength('Lorem ipsum dolor sit amet.', 30, 1));
debug("checkLength(56, 30, 1)", Formation::checkLength('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 30));

// Check match
debug("checkMatch(1, '1')", Formation::checkMatch(1, 1));
debug("checkMatch(1, '1', true)", Formation::checkMatch(1, '1', true));

// Custom regex
debug("custom('foo', '/(foo|bar)/')", Formation::custom('foo', '/(foo|bar)/'));
debug("custom('baz', '/(foo|bar)/')", Formation::custom('baz', '/(foo|bar)/'));

// Is letters, numeric, punctuation (english qwerty keyboards)
debug("isAllChars('abcdefghijklmnopqrstuvwxyz')", Formation::isAllChars('abcdefghijklmnopqrstuvwxyz'));
debug("isAllChars('1234567890')", Formation::isAllChars('1234567890'));
debug("isAllChars('~!@#$%^&*()_+-=][}{\|\';\":.,/?')", Formation::isAllChars('~!@#$%^&*()_+-=][}{\|\';":.,></?'));
debug("isAllChars('0')", Formation::isAllChars('0'));

// Is letters, numeric (english qwerty keyboards)
debug("isAlnum('abcdefghijklmnopqrstuvwxyz')", Formation::isAlnum('abcdefghijklmnopqrstuvwxyz'));
debug("isAlnum('1234567890')", Formation::isAlnum('1234567890'));
debug("isAlnum('~!@#$%^&*()_+-=][}{\|\';\":.,/?')", Formation::isAlnum('~!@#$%^&*()_+-=][}{\|\';":.,></?'));

// Is letters (english qwerty keyboards)
debug("isAlpha('abcdefghijklmnopqrstuvwxyz')", Formation::isAlpha('abcdefghijklmnopqrstuvwxyz'));
debug("isAlpha('1234567890')", Formation::isAlpha('1234567890'));
debug("isAlpha('~!@#$%^&*()_+-=][}{\|\';\":.,/?')", Formation::isAlpha('~!@#$%^&*()_+-=][}{\|\';":.,></?'));

// Is boolean
debug("isBoolean(1)", Formation::isBoolean(1));
debug("isBoolean(false)", Formation::isBoolean(false));
debug("isBoolean('1')", Formation::isBoolean('1'));

// Is date
debug("isDate('02/26/1988')", Formation::isDate('02/26/1988'));
debug("isDate('26/02/1988')", Formation::isDate('26/02/1988'));
debug("isDate('1988/02/26')", Formation::isDate('1988/02/26'));
debug("isDate('february 26th')", Formation::isDate('february 26th'));
debug("isDate('foobar')", Formation::isDate('foobar'));

// Is decimal
debug("isDecimal('10.15')", Formation::isDecimal('10.15'));
debug("isDecimal('12.1')", Formation::isDecimal('12.1'));
debug("isDecimal('12.100', 3)", Formation::isDecimal('12.100', 3));
debug("isDecimal('foobar')", Formation::isDecimal('foobar'));

// Is email
debug("isEmail('test@domain.com')", Formation::isEmail('test@domain.com'));
debug("isEmail('test123@domain.co.uk')", Formation::isEmail('test@domain.co.uk'));
debug("isEmail('test+plus@domain.me')", Formation::isEmail('test+plus@domain.me'));
debug("isEmail('foobar')", Formation::isEmail('foobar'));

// Is extension
debug("isExt('file.jpg')", Formation::isExt('file.jpg'));
debug("isExt('file.zip')", Formation::isExt('file.zip'));
debug("isExt(array('name' => 'file.jpg'))", Formation::isExt(array('name' => 'file.jpg')));
debug("isExt('file.txt', array('txt'))", Formation::isExt('file.txt', array('txt')));

// Is IP
debug("isIp('127.0.0.1')", Formation::isIp('127.0.0.1'));
debug("isIp('99.199.185.57')", Formation::isIp('99.199.185.57'));
debug("isIp('71.36.755.181')", Formation::isIp('71.36.775.181'));

// Is numeric
debug("isNumeric(12345)", Formation::isNumeric(12345));
debug("isNumeric('67890')", Formation::isNumeric('67890'));
debug("isNumeric('1337.00', '.')", Formation::isNumeric('1337.00', '.'));
debug("isNumeric('abcdef')", Formation::isNumeric('abcdef'));

// Is phone number
debug("isPhone('(123) 456-7890')", Formation::isPhone('(123) 456-7890'));
debug("isPhone('456-7890')", Formation::isPhone('456-7890'));

// Is time
debug("isTime('12:12:12 PM')", Formation::isTime('12:12:12 PM'));
debug("isTime('05:10 AM')", Formation::isTime('05:10 AM'));
debug("isTime('23:11')", Formation::isTime('23:11'));

// Is website
debug("isWebsite('http://milesj.me')", Formation::isWebsite('http://milesj.me'));
debug("isWebsite('http://www.milesj.me')", Formation::isWebsite('http://www.milesj.me'));
debug("isWebsite('http://www.milesj.me/code/formation')", Formation::isWebsite('http://www.milesj.me/code/formation'));
debug("isWebsite('http://sub.sub.milesj.me/some/path.html')", Formation::isWebsite('http://sub.sub.milesj.me/some/path.html'));
debug("isWebsite('http://sub.sub.milesj.co.uk/some/path.html?query=param')", Formation::isWebsite('http://sub.sub.milesj.co.uk/some/path.html?query=param'));

// Is in list
debug("inList('foo')", Formation::inList('foo'));
debug("inList('foo', array('bar'))", Formation::inList('foo', array('bar')));
debug("inList('foo', array('foo', 'bar'))", Formation::inList('foo', array('foo', 'bar')));

// In range
debug("inRange(27, 30, 1)", Formation::inRange(27, 30, 1));
debug("inRange(56, 30, 1)", Formation::inRange(56, 30));

// Not empty
debug("notEmpty('0')", Formation::notEmpty('0'));
debug("notEmpty(12345)", Formation::notEmpty(12345));
debug("notEmpty('foo')", Formation::notEmpty('foo'));
debug("notEmpty('')", Formation::notEmpty(''));
