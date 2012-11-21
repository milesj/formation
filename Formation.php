<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/formation
 */

namespace mjohnson\formation;

use \Exception;

/**
 * Contains static validation methods.
 *
 * @version	3.2.1
 * @package	mjohnson.formation
 */
class Formation {

	/**
	 * Checks the strings length of characters.
	 *
	 * @access public
	 * @param string $input
	 * @param int $max
	 * @param int $min
	 * @return mixed
	 * @static
	 */
	public static function checkLength($input, $max = 2500, $min = 1) {
		$length = mb_strlen($input);

		return ($length <= $max && $length >= $min);
	}

	/**
	 * Checks to see if two strings match.
	 *
	 * @access public
	 * @param string $input
	 * @param string $match
	 * @param boolean $strict
	 * @return mixed
	 * @static
	 */
	public static function checkMatch($input, $match, $strict = false) {
		if ($strict) {
			return ($input === $match);
		}

		return ($input == $match);
	}

	/**
	 * Matches a custom regex.
	 *
	 * @access public
	 * @param string $input
	 * @param string $expression
	 * @return mixed
	 * @static
	 */
	public static function custom($input, $expression = '') {
		if (empty($expression)) {
			return false;
		}

		return preg_match($expression, $input);
	}

	/**
	 * Validate an images dimensions.
	 *
	 * @access public
	 * @param array $input
	 * @param string $type
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function dimensions($input, $type, $size) {
		if (self::isFile($input)) {
			$file = getimagesize($input['tmp_name']);

			if (!$file) {
				return false;
			}

			$width = $file[0];
			$height = $file[1];
			$size = (int) $size;
			$result = false;

			switch ($type) {
				case 'maxWidth':    $result = ($width <= $size); break;
				case 'maxHeight':   $result = ($height <= $size); break;
				case 'minWidth':    $result = ($width >= $size); break;
				case 'minHeight':   $result = ($height >= $size); break;
			}

			return $result;
		}

		return false;
	}

	/**
	 * Escapes characters that would break the regex.
	 *
	 * @access public
	 * @param array|string $characters
	 * @return string
	 */
	public static function escape($characters) {
		if (is_array($characters)) {
			$characters = implode('', $characters);
		}

		return preg_quote($characters, '/');
	}

	/**
	 * Checks to see if a value is of numerical, alphabetical and punctuational value.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isAllChars($input) {
		$exceptions = self::escape(array('!','@','#','$','%','^','&','*','(',')','-','_','=','+','~','`','[',']','{','}','\\','|',';',':','"',"'",'?','/','.','>','<',','));

		return preg_match('/^[\s0-9a-zA-Z' . $exceptions . ']+$/is', $input);
	}

	/**
	 * Checks to see if a required field is of numerical and alphabetical value.
	 *
	 * @access public
	 * @param string $input
	 * @param array $exceptions
	 * @return mixed
	 * @static
	 */
	public static function isAlnum($input, $exceptions = array()) {
		return preg_match('/^[a-zA-Z0-9\s' . self::escape($exceptions) . ']+$/', $input);
	}

	/**
	 * Checks to see if a required field is of alphabetical value.
	 *
	 * @access public
	 * @param string $input
	 * @param array $exceptions
	 * @return mixed
	 * @static
	 */
	public static function isAlpha($input, $exceptions = array()) {
		return preg_match('/^[a-zA-Z\s' . self::escape($exceptions) . ']+$/', $input);
	}

	/**
	 * Checks to see if a value is boolean.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isBoolean($input) {
		return in_array($input, array(1, 0, '1', '0', true, false), true);
	}

	/**
	 * Checks to see if a value is a valid date: mm/dd/yyyy.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isDate($input) {
		if (($timestamp = strtotime($input)) === false) {
			return false;
		}

		list($m, $d, $y) = explode('/', date('m/d/Y', $timestamp));

		return (preg_match('/^(?:0?[1-9]|1[0-2])\/(?:0?[1-9]|[1-2]\d|3[0-1])\/(?:\d{2,4})$/', $input) || checkdate($m, $d, $y));
	}

	/**
	 * Checks to see if a value has a decimal value.
	 *
	 * @access public
	 * @param string $input
	 * @param int $decimals
	 * @return mixed
	 * @static
	 */
	public static function isDecimal($input, $decimals = 2) {
		return preg_match('/^[-]*[0-9][0-9]*\.[0-9]{' . intval($decimals) . '}$/', $input);
	}

	/**
	 * Checks to see if a value is a valid email address.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isEmail($input) {
		return preg_match('/^[\+0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i', mb_strtolower($input));
	}

	/**
	 * Checks to see if a file input is not empty. Use in place of notEmpty() for files.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isFile($input) {
		return (is_array($input) && !empty($input['tmp_name']) && $input['error'] == 0);
	}

	/**
	 * Checks to see if a value has a correct extension.
	 *
	 * @access public
	 * @param string $input
	 * @param array $extensions
	 * @return mixed
	 * @static
	 */
	public static function isExt($input, $extensions = array()) {
		if (empty($extensions) || !is_array($extensions)) {
			$extensions = array('gif', 'jpeg', 'png', 'jpg');
		}

		$field = is_array($input) ? $input['name'] : $input;
		$ext = mb_strtolower(trim(mb_strrchr($field, '.'), '.'));

		return in_array($ext, $extensions, true);
	}

	/**
	 * Checks to see if a value is a valid ip address.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isIp($input) {
		return preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $input);
	}

	/**
	 * Checks to see if a required field is of numerical value.
	 *
	 * @access public
	 * @param string $input
	 * @param array $exceptions
	 * @return mixed
	 * @static
	 */
	public static function isNumeric($input, $exceptions = array()) {
		return preg_match('/^[0-9\s' . self::escape($exceptions) . ']+$/', $input);
	}

	/**
	 * Checks to see if a value is a valid phone number: (xxx) xxx-xxxx.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isPhone($input) {
		return preg_match('/^\([0-9]{3}\)\s[0-9]{3}[-]?[0-9]{4}$/', $input);
	}

	/**
	 * Checks to see if a value is a valid timestamp: hh:mm:ss am/pm.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isTime($input) {
		if (($timestamp = strtotime($input)) === false) {
			return false;
		}

		return preg_match('/^(?:0?[0-9]|1[0-2]):(?:[0-5][0-9])(?::[0-5][0-9])? (PM|AM)$/', date('h:i:s A', $timestamp));
	}

	/**
	 * Checks to see if a value is a valid website address: http://.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function isWebsite($input) {
		return preg_match('/^(?:(?:http|ftp)s?):\/\/(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,4}(?:[-a-zA-Z0-9._\/&=+%?]+)?$/', mb_strtolower($input));
	}

	/**
	 * Checks to see if an item is within an array/list.
	 *
	 * @access public
	 * @param string $input
	 * @param array $list
	 * @return mixed
	 * @static
	 */
	public static function inList($input, $list = array()) {
		if (!is_array($list)) {
			return false;
		}

		return in_array($input, $list, true);
	}

	/**
	 * Checks to see if a number is within a range.
	 *
	 * @access public
	 * @param string $input
	 * @param int $max
	 * @param int $min
	 * @return mixed
	 * @static
	 */
	public static function inRange($input, $max, $min = 1) {
		return ($input <= $max && $input >= $min);
	}

	/**
	 * Validate an images filesize is above the minimum.
	 *
	 * @access public
	 * @param array $input
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function minFilesize($input, $size = 0) {
		if (empty($size) || !is_numeric($size)) {
			$size = 0;
		}

		if (self::isFile($input)) {
			return ($input['size'] > $size);
		}

		return false;
	}

	/**
	 * Validate an images height is above the minimum.
	 *
	 * @access public
	 * @param array $input
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function minHeight($input, $size = 0) {
		return self::dimensions($input, 'minHeight', $size);
	}

	/**
	 * Validate an images width is above the minimum.
	 *
	 * @access public
	 * @param array $input
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function minWidth($input, $size = 0) {
		return self::dimensions($input, 'minWidth', $size);
	}

	/**
	 * Validate an images filesize is below the maximum.
	 *
	 * @access public
	 * @param array $input
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function maxFilesize($input, $size = 5242880) {
		if (empty($size) || !is_numeric($size)) {
			$size = 5242880;
		}

		if (self::isFile($input)) {
			return ($input['size'] <= $size);
		}

		return false;
	}

	/**
	 * Validate an images height is below the maximum.
	 *
	 * @access public
	 * @param array $input
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function maxHeight($input, $size = 0) {
		return self::dimensions($input, 'maxHeight', $size);
	}

	/**
	 * Validate an images width is below the maximum.
	 *
	 * @access public
	 * @param array $input
	 * @param int $size
	 * @return mixed
	 * @static
	 */
	public static function maxWidth($input, $size = 0) {
		return self::dimensions($input, 'maxWidth', $size);
	}

	/**
	 * Checks to see if a required field is empty.
	 *
	 * @access public
	 * @param string $input
	 * @return mixed
	 * @static
	 */
	public static function notEmpty($input) {
		return ((string) $input != '');
	}

}
