<?php
/**
 * form.php
 *
 * A form validation and builder class that can build form elements and then validates form inputs for errors and returns a cleaned result.
 * 
 * @author		Miles Johnson - www.milesj.me
 * @copyright	Copyright 2006-2009, Miles Johnson, Inc.
 * @license		http://www.opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @package		Formation - Builder and Validator
 * @version		v2.2
 * @link		www.milesj.me/resources/script/form-manager
 */
 
class Form extends Formation { 

	/**
	 * Current version: www.milesj.me/files/logs/form-manager
	 *
	 * @access public
	 * @var int
	 */
	public $version = '2.2';
	
	/**
	 * The current forms id.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_current; 

	/**
	 * The current doctype.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_doctype = 'html';
	
	/**
	 * List of used forms in this request.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_existent = array();
	
	/**
	 * All tags for form creation.
	 *
	 * @access private
	 * @var array
	 */
	private $__tags = array( 
		'form_open'		=> '<form%s>',
		'form_close'	=> '</form>',
		'input'			=> array('<input%s>', '<input%s />'),
		'textarea'		=> '<textarea%s>%s</textarea>',
		'select'		=> '<select%s>%s</select>',
		'option'		=> '<option%s>%s</option>',
		'optgroup_open'	=> '<optgroup%s>',
		'optgroup_close'=> '</optgroup>',
		'fieldset_open'	=> '<fieldset%s>',
		'fieldset_close'=> '</fieldset>',
		'legend'		=> '<legend%s>%s</legend>',
		'label'			=> '<label%s>%s</label>'
	);
	
	/**
	 * Initialize the class.
	 *
	 * @access public
	 * @param boolean $xhtml
	 * @return void
	 */
	public function __construct($xhtml = false) {
		parent::__construct();
		
		if ($xhtml === true) {
			$this->_doctype = 'xhtml';
		}
	}

	/**
	 * Close the form.
	 *
	 * @access public
	 * @return string
	 */
	public function close() {
		$return = $this->__tags['form_close'];
		if ($this->_doctype == 'xhtml') {
			$return = $this->__tags['fieldset_close'] . $return;
		}
		return $return;
	}
	
	/**
	 * Create the form and the opening tag.
	 *
	 * @access public
	 * @param string $form
	 * @param array $attributes
	 * @return string
	 */
	public function create($form = 'Form', $attributes = array()) {
		if (empty($form)) {
			$form = 'Form';
		}
		
		$form = $this->inflect($form);
		
		if (in_array($form, $this->_existent)) {
			$form = $form . count($this->_existent);
		}
		
		$this->_current = $form;
		$this->_existent[] = $form;

		$attributes = array_merge(array(
			'id' => $this->_current .'Form',
			'action' => '',
			'method' => 'post'
		), $attributes);
		
		if (isset($attributes['type'])) {
			if ($attributes['type'] == 'file') {
				$attributes['enctype'] = 'multipart/form-data';
			} else if ($attributes['type'] == 'app') {
				$attributes['enctype'] = 'application/x-www-form-urlencoded';
			}
			
			unset($attributes['type']);
		}
		
		$return = sprintf($this->__tags['form_open'], $this->_attributes($attributes));
		if ($this->_doctype == 'xhtml') {
			$return .= sprintf($this->__tags['fieldset_open'], '');
			
			if (isset($attributes['legend'])) {
				$return .= sprintf($this->__tags['legend'], '', $attributes['legend']);
			}
		}
		
		return $return;
	}
	
	/**
	 * Inflect a name to use for the form element IDs.
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	public function inflect($value) {
		return ucfirst(preg_replace('/[^-_a-zA-Z0-9]/i', '', $value));
	}
	
	/**
	 * Create the input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function input($name, $attributes = array()) {
		if (!empty($attributes['options']) && is_array($attributes['options'])) {
			$type = 'select';
		} else {
			$type = (isset($attributes['type'])) ? $attributes['type'] : 'text';
		}
		
		// Default value
		$default = null;
		if (isset($attributes['default'])) {
			$default = $attributes['default'];
			if ($type != 'select') {
				unset($attributes['default']);
			}
		}
		
		if ($type == 'select' && !isset($attributes['default'])) {
			$attributes['default'] = $default;
		}
		
		// Disabled, read only
		if (isset($attributes['disabled'])) {
			if ($attributes['disabled'] === true || $attributes['disabled'] == 'disabled') {
				$attributes['disabled'] = 'disabled';
			} else {
				unset($attributes['disabled']);
			}
		}
		
		if (isset($attributes['readonly'])) {
			if ($attributes['readonly'] === true || $attributes['readonly'] == 'readonly') {
				$attributes['readonly'] = 'readonly';
			} else {
				unset($attributes['readonly']);
			}
		}
		
		// Determine value
		$value = null;
		if (($type == 'checkbox' || $type == 'radio') && !isset($attributes['value'])) {
			trigger_error('Form::input(): When using the checkbox/radio type, the value option is required', E_USER_WARNING); 
		} else if (isset($attributes['value'])) {
			$value = $attributes['value'];
			unset($attributes['value']);
		}
		
		// Attributes
		$id = $this->_current . $this->inflect($name);
		if (isset($attributes['id'])) {
			$id = $this->_current . $this->inflect($attributes['id']);
			unset($attributes['id']);
		}
		
		$attributes = array_merge(array(
			'id' 	=> $id,
			'type' 	=> $type,
			'name' 	=> $name,
			'value' => $value
		), $attributes);
		
		// Value processing
		if ($type != 'select') {
			$result = $this->value($type, $name, $value, $default);
			if ($result === true) {
				if ($type == 'checkbox' || $type == 'radio') {
					$attributes['checked'] = 'checked';
					$attributes['value'] = $value;
				}
			} else if (!empty($result)) {
				$attributes['value'] = $result;
			}
		}
			
		// Apply error and classes
		$class = $this->getClass($name);
		if (!empty($class)) {
			if (!empty($attributes['class'])) {
				$class = $class .' '. $attributes['class'];
			}
			$attributes['class'] = $class;
		}
		
		$method = "_". $type;
		if ($type == 'password' || $type == 'hidden' || $type == 'file' || $type == 'image') {
			$method = '_text';
		}
		
		return $this->{$method}($attributes);
	}
	
	/**
	 * Form input label.
	 *
	 * @access public
	 * @param string $name
	 * @param string $title
	 * @param string $attributes
	 * @return string
	 */
	public function label($name, $title, $attributes = array()) {
		$attributes = array_merge(array(
			'for' => $this->_current . $this->inflect($name)
		), $attributes);
		
		return sprintf($this->__tags['label'], $this->_attributes($attributes), $title);
	}
	
	/**
	 * Form reset button.
	 *
	 * @access public
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	public function reset($text = 'Reset', $attributes = array()) {
		$attributes = array_merge(array(
			'id' 	=> $this->_current .'ResetButton',
			'type' 	=> 'reset',
			'value' => $text
		), $attributes);
		
		$tag = ($this->_doctype === 'html') ? $this->__tags['input'][0] : $this->__tags['input'][1];
		return sprintf($tag, $this->_attributes($attributes));	
	}
	
	/**
	 * Form submit button.
	 *
	 * @access public
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	public function submit($text = 'Submit', $attributes = array()) {
		$attributes = array_merge(array(
			'id' 	=> $this->_current .'SubmitButton',
			'type' 	=> 'submit',
			'value' => $text
		), $attributes);
		
		$tag = ($this->_doctype === 'html') ? $this->__tags['input'][0] : $this->__tags['input'][1];
		return sprintf($tag, $this->_attributes($attributes));	
	}
	
	/**
	 * Format the attributes.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _attributes($attributes) {
		$clean = array();
		if (!empty($attributes)) {
			foreach ($attributes as $att => $value) {
				if ($att != 'value') {
					$value = htmlentities($value, ENT_NOQUOTES, 'UTF-8');
				}
				
				$clean[] = $att .'="'. $value .'"';
			}
		}
		return ' '. implode(' ', $clean);
	}
	
	/**
	 * Form checkbox.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _checkbox($attributes = array()) {
		$tag = ($this->_doctype === 'html') ? $this->__tags['input'][0] : $this->__tags['input'][1];
		return sprintf($tag, $this->_attributes($attributes));
	}
	
	/**
	 * Form select options.
	 *
	 * @access protected
	 * @param string $value
	 * @param string $option
	 * @param string $name
	 * @param string $default
	 * @return string
	 */
	protected function _options($value, $option, $name, $default) {
		$attributes = array('value' => $value);
		$result = $this->value('select', $name, $value, $default);
		
		if ($result === true) {
			$attributes['selected'] = 'selected';
		}
		
		return sprintf($this->__tags['option'], $this->_attributes($attributes), $option);
	}
	
	/**
	 * Form radio button.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _radio($attributes = array()) {
		$tag = ($this->_doctype === 'html') ? $this->__tags['input'][0] : $this->__tags['input'][1];
		return sprintf($tag, $this->_attributes($attributes));
	}
	
	/**
	 * Form select field.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _select($attributes = array()) {
		$options = array();
		
		if (isset($attributes['empty'])) {
			$empty = array('value' => '');
			$emptyText = '';
			
			if (is_string($attributes['empty'])) {
				$emptyText = $attributes['empty'];
			}
			
			$options[] = sprintf($this->__tags['option'], $this->_attributes($empty), $emptyText);
			unset($attributes['empty']);
		}
		
		if (!empty($attributes['options'])) {
			foreach ($attributes['options'] as $value => $option) {
				if (is_array($option)) {
					$options[] = sprintf($this->__tags['optgroup_open'], $this->_attributes(array('label' => $value)));
					foreach ($option as $v => $o) {
						$options[] = $this->_options($v, $o, $attributes['name'], $attributes['default']);
					}
					$options[] = $this->__tags['optgroup_close'];
				} else {
					$options[] = $this->_options($value, $option, $attributes['name'], $attributes['default']);
				}
			}
			
			unset($attributes['options']);
		}
		
		unset($attributes['type'], $attributes['default'], $attributes['value']);
		
		return sprintf($this->__tags['select'], $this->_attributes($attributes), implode("\n", $options));
	}
	
	/**
	 * The text input field.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _text($attributes = array()) {
		$tag = ($this->_doctype === 'html') ? $this->__tags['input'][0] : $this->__tags['input'][1];
		return sprintf($tag, $this->_attributes($attributes));
	}
	
	/**
	 * The textarea input field.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _textarea($attributes = array()) {
		$attributes = array_merge(array(
			'cols' => 25, 'rows' => 5,
		), $attributes);
		
		$value = $attributes['value'];
		unset($attributes['value'], $attributes['type']);
		
		return sprintf($this->__tags['textarea'], $this->_attributes($attributes), $value);	
	}
	
}

class Formation {

	/**
	 * Array of all cleaned post inputs.
	 *
	 * @access private
	 * @var array
	 */
	private $__cleaned;

	/**
	 * Array of errored post inputs; number of total errors.
	 *
	 * @access private
	 * @var array
	 */
	private $__errors;
	
	/**
	 * Array of $_POST or $_GET.
	 *
	 * @access private
	 * @var array
	 */
	private $__post;
	
	/**
	 * Reset all arrays in the constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() { 
		$this->resetData();
	}
	
	/**
	 * Filters and cleans each input and applies it to the cleaned array.
	 *
	 * @access public
	 * @param array $inputs
	 * @param boolean $removeHtml
	 * @return array
	 */
	public function clean($inputs = '', $removeHtml = true) {
		if (empty($inputs)) {
			$inputs = $this->__post;
		}
		
		if (is_array($inputs)) {
			foreach ($inputs as $input => $value) {
				if (array_key_exists($input, $this->__post)) {
					if ($this->__post[$input] != '') {
						$this->__cleaned[$input] = $this->cleanse($this->__post[$input], $removeHtml);
					} else {
						$this->__cleaned[$input] = '';
					}
				}
			}
		}
		
		$cleaned = $this->getCleaned();
		return $cleaned;
	}
	
	/**
	 * Escapes unwanted characters and tags; options for escaping quotes.
	 *
	 * @access public
	 * @param string $toClean
	 * @param boolean $removeHtml
	 * @return string
	 * @static
	 */
	public static function cleanse($toClean, $removeHtml = true) {
		if (is_array($toClean)) {
			foreach ($toClean as $key => $value) {
				$toClean[$key] = self::cleanse($value, $removeHtml);
			}
		} else {
			$toClean = trim(urldecode($toClean));
		
			if ($removeHtml === true) {		
				$toClean = htmlentities(strip_tags($toClean), ENT_NOQUOTES, 'UTF-8'); 
			}
		}
		
		return $toClean;
	}
	
	/**
	 * Add to the error list.
	 *
	 * @access public
	 * @param string $input
	 * @param string $message
	 * @return boolean
	 */
	public function error($input, $message) {
		$this->__errors[$input] = $message;
		return false;
	}
	
	/**
	 * Escapes characters that would break the regex.
	 *
	 * @access public
	 * @param array $characters
	 * @return string
	 */
	public static function escape($characters) {
		$escaped = preg_quote(implode('', $characters), '/');		
		return $escaped;
	}
	
	/**
	 * Returns a value from the post.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key = '') {
		if (isset($this->__post[$key])) {
			return $this->__post[$key];
		} else if (!empty($this->__post)) {
			return $this->__post;
		}
		
		return;
	}
	
	/**
	 * Returns the total errors and array of error messages.
	 *
	 * @access public
	 * @return int
	 */
	public function getErrors() {
		return $this->__errors;
	}
	
	/**
	 * If an input doesnt validate, apply a class to the input.
	 *
	 * @access public
	 * @param string $input - Check to see if this input failed
	 * @param string $class - The error class to return
	 * @return string
	 */
	public function getClass($input, $class = 'input-error') {
		if (!empty($this->__errors)) {
			return (in_array($input, array_keys($this->__errors))) ? $class : '';
		}
		return;
	}
	
	/**
	 * Return the array of all cleaned elements.
	 *
	 * @access public
	 * @return string
	 */
	public function getCleaned() {
		return $this->__cleaned;
	}
	
	/**
	 * Checks to see if the post is submitted; also saves the post property.
	 *
	 * @access public
	 * @param array $post 		- The $_POST or $_GET
	 * @param string $submit 	- Name of the submit button (optional)
	 * @return boolean
	 */
	public function process($post, $submit = null) {
		$this->resetData();
		$this->__post = $post;
		
		if ((!empty($submit) && isset($this->__post[$submit])) || (empty($submit) && !empty($this->__post))) {
			return true;
		} else {
			$this->__post = array();
			return false;
		}
	}
	
	/**
	 * Resets all values.
	 *
	 * @access public
	 * @return void
	 */
	public function resetData() {
		$this->__post = array();
		$this->__errors = array();
		$this->__cleaned = array();
	}
	
	/**
	 * Checks to see if there are no errors and validates.
	 *
	 * @access public
	 * @param array $schema
	 * @return boolean
	 */
	public function validates($schema = array()) {
		if (!empty($schema)) {
			foreach ($schema as $input => $validations) {
				$required = true;
				if (isset($validations['required'])) {
					$required = $validations['required'];
					unset($validations['required']);
				}
				
				$this->_build($input, $validations, $required);
			}
		}
		
		return (count($this->__errors) === 0) ? true : false;
	}
	
	/**
	 * If an input is set with a value keep it, or display default.
	 *
	 * @access public
	 * @param string $type		- The type of form element to check against
	 * @param string $input		- The input value to check against
	 * @param string $value		- The value your are submitting
	 * @param string $default	- Default value to display
	 * @return string
	 */
	public function value($type, $input, $value = '', $default = '') {
		$input = preg_replace('/[^a-zA-Z0-9]/i', '', $input);
		$input = (isset($this->__post[$input])) ? $this->__post[$input] : null;
		$return = '';
		
		switch ($type) {
			default:
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
			case 'file':
				if (!empty($input)) {
					$return = self::cleanse($input);
				} else {
				 	$return = (isset($default)) ? $default : '';
				}
			break;
			case 'select':
				if (!empty($input) && $input == $value) {
					$return = true;
				} else {
					$return = ($value == $default) ? true : false;
				}
			break;
			case 'radio':
				if (!empty($input)) {
					$return = ($input == $value) ? true : false;
				} else {
					$return = ($value == $default || $default === true) ? true : false;
				}
			break;
			case 'checkbox':
				$return = false;
				
				if (is_array($input)) {
					if (isset($input) && in_array($value, $input)) {
						$return = true;
					}	
				} else {
					if (isset($input) && $input == $value) {
						$return = true;
					} else {
						$return = ($value == $default || $default === true) ? true : false;
					}	
				}
			break;
		}
		
		return $return;
	}

	/**
	 * Checks to see if a required field is empty.
	 *
	 * @access public
	 * @param string $input
	 * @param string $message
	 * @return mixed
	 * @static
	 */
	public static function notEmpty($input, $message) {
		return (empty($input) && !isset($input)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is of numerical, alphabetical and punctuational value.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	 
	public static function isAllChars($input, $message) {
		$exceptions = self::escape(array('!','@','#','$','%','^','&','*','(',')','-','_','=','+','~','`','[',']','{','}','\\','|',';',':','"',"'",'?','/','.','>','<',','));
		return (!preg_match('/^[a-zA-Z0-9\s'. $exceptions .']+$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a required field is of numerical and alphabetical value.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @param array $exceptions
	 * @return mixed
     * @static
     */
	public static function isAlnum($input, $message, $exceptions = '') {
		if (is_array($exceptions)) {
			$exceptions = self::escape($exceptions);
		}
		
		return (!preg_match('/^[a-zA-Z0-9\s'. $exceptions .']+$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a required field is of alphabetical value.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @param array $exceptions
	 * @return mixed
     * @static
     */
	public static function isAlpha($input, $message, $exceptions = '') {
		if (is_array($exceptions)) {
			$exceptions = self::escape($exceptions);
		}
		
		return (!preg_match('/^[a-zA-Z\s'. $exceptions .']+$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is boolean.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isBoolean($input, $message) {
		$types = array(0, 1, '0', '1', true, false);
		return (!in_array($input, $types, true)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is a valid date: mm/dd/yyyy.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isDate($input, $message) {
		list($m, $d, $y) = explode('/', $input);
		return (!preg_match('/^(?:0?[1-9]|1[0-2])\/(?:0?[1-9]|[1-2]\d|3[0-1])\/(?:\d{2,4})$/', $input) || !checkdate($m, $d, $y)) ? false : $input;
	}
	
	/**
     * Checks to see if a value has a decimal value.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param int $decimals
     * @return mixed
     * @static
     */
	public static function isDecimal($input, $message, $decimals = 2) {
		return (!preg_match('/^[-]*[0-9][0-9]*\.[0-9]{'. intval($decimals) .'}$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is a valid email address.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isEmail($input, $message) {
		return (!preg_match('/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i', mb_strtolower($input))) ? false : $input;
	}
	
	/**
     * Checks to see if a value has a correct extension.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param string $extensions
     * @return mixed
     * @static
     */
	public static function isExt($input, $message, $extensions = null) {
		if (!is_array($extensions)) {
			$extensions = array('gif', 'jpeg', 'png', 'jpg');
		}
		$ext = mb_strtolower(trim(mb_strrchr($input, '.'), '.'));
		return (!in_array($ext, $extensions, true)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is a valid ip address.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isIp($input, $message) {
		return (!preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a required field is of numerical value.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param array $exceptions
     * @return mixed
     * @static
     */
	public static function isNumeric($input, $message, $exceptions = '') {
		if (is_array($exceptions)) {
			$exceptions = self::escape($exceptions);
		}
		
		return (!preg_match('/^[0-9\s'. $exceptions .']+$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is a valid phone number: (xxx) xxx-xxxx.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isPhone($input, $message) {
		return (!preg_match('/^\([0-9]{3}\)\s[0-9]{3}[-]?[0-9]{4}$/', $input)) ? false : $input;
	}
	
	/**
     * Checks to see if a value is a valid timestamp: hh:mm:ss am/pm.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isTime($input, $message) {
		return (!preg_match('/^(?:0?[1-9]|1[0-2]):(?:[0-5][0-9])(?::[0-5][0-9])? [PA]\.?M\.?$/', mb_strtoupper($input))) ? false : $input;
	}
	
	/**
     * Checks to see if a value is a valid website address: http://.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
     * @return mixed
     * @static
     */
	public static function isWebsite($input, $message) {
		return (!preg_match('/^(?:(?:http|ftp)s?):\/\/(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,4}(?:[-a-zA-Z0-9._\/&=+%?]+)?$/', mb_strtolower($input))) ? false : $input;
	}
	
	/**
     * Checks to see if an item is within an array/list.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param string $list
     * @return mixed
     * @static
     */
	public static function inList($input, $message, $list = array()) {
		if (!is_array($list)) {
			return false;
		}
		return (!in_array($input, $list, true)) ? false : $input;
	}
	
	/**
     * Checks to see if a number is within a range.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param int $max
	 * @param int $min
     * @return mixed
     * @static
     */
	public static function inRange($input, $message, $max, $min) {
		return ($input > $max || $input < $min) ? false : $input;
	}
	
	/**
     * Checks the strings length of characters.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param int $max
	 * @param int $min
     * @return mixed
     * @static
     */
	public static function checkLength($input, $message, $max = 2500, $min = 1) {
		$length = mb_strlen($input);
		return ($length > $max || $length < $min) ? false : $input;
	}
	
	/**
     * Checks to see if two strings match.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param string $match
	 * @param boolean $strict
     * @return mixed
     * @static
     */
	public static function checkMatch($input, $message, $match, $strict = false) {
		if ($strict) {
			return ($input !== $match) ? false : $input;	
		} else {
			return ($input != $match) ? false : $input;
		}
	}
	
	/**
     * Matches a custom regex.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param string $expression
     * @return mixed
     * @static
     */
	public static function custom($input, $message, $expression = '') {
		if (empty($expression)) {
			return false;
		}
		return (!preg_match($expression, $input)) ? false : $input;
	}
	
	/**
	 * Builds the loop for the following input.
	 *
	 * @access protected
	 * @param string $input
	 * @param array $cleaners
	 * @param boolean $required - Is input required?
	 * @return mixed
	 */
	protected function _build($input, $cleaners, $required = true) {
	 	if ($required === true || $required === false && !empty($this->__post[$input])) {
			foreach ($cleaners as $method => $args) {
				if (!in_array($method, get_class_methods($this))) {
					trigger_error('Formation::_build(): Cleaning method "'. $method .'" does not exist', E_USER_WARNING);
					
				} else if (!is_array($args) && !empty($args)) {
					$args = array($args);
				}
	
				if (!$this->_parse($input, $method, $args)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Parses the input to each specified method.
	 * 
	 * @access protected
	 * @param string $input
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	protected function _parse($input, $method, $args) {
		$arguments = array();
		$arguments[] = (isset($this->__post[$input])) ? $this->__post[$input] : null;
			
		if (is_array($args)) {
			foreach ($args as $index => $value) {
				$arguments[] = $value;
			}
		}

		if (!call_user_func_array(array('Formation', $method), $arguments)) {
			if (!empty($arguments[1])) {
				return $this->error($input, $arguments[1]);
			} else {
				trigger_error('Formation::_parse(): The method "'. $method .'" to call on "'. $input .'" failed to initiate.', E_USER_WARNING);
				return false;
			}
		}
		
		return true;
	}
	
}
