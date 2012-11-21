<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/formation
 */

namespace mjohnson\formation;

use \Exception;

require_once 'Formation.php';

/**
 * A class that builds form elements and then validates their input values for errors.
 * Once validated, a cleaned data result is returned, or an array of errors are returned.
 *
 * @version	4.0.0
 * @package	mjohnson.formation
 */
class Form {

	/**
	 * Array of cleaned values.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_cleaned = array();

	/**
	 * Configuration settings.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_config = array(
		'xhtml' => false,
		'legend' => false,
		'model' => 'Form'
	);

	/**
	 * Array of errored form fields.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * Array of $_POST or $_GET, merged with $_FILES.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_data = array();

	/**
	 * All tags for form creation.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_tags = array(
		'form_open'         => '<form%s>',
		'form_close'        => '</form>',
		'input'             => array('<input%s>', '<input%s />'),
		'textarea'          => '<textarea%s>%s</textarea>',
		'select'            => '<select%s>%s</select>',
		'option'            => '<option%s>%s</option>',
		'optgroup_open'     => '<optgroup%s>',
		'optgroup_close'    => '</optgroup>',
		'fieldset_open'     => '<fieldset%s>',
		'fieldset_close'    => '</fieldset>',
		'legend'            => '<legend%s>%s</legend>',
		'label'             => '<label%s>%s</label>',
		'button'            => '<button%s>%s</button>'
	);

	/**
	 * Initialize the class.
	 *
	 * @access public
	 * @param string $model
	 * @param boolean $xhtml
	 */
	public function __construct($model = 'Form', $xhtml = false) {
		$this->flush();

		if (!$model || is_numeric($model)) {
			$model = 'Form';
		}

		$this->_config['model'] = $this->inflect((string) $model);
		$this->_config['xhtml'] = (boolean) $xhtml;
	}

	/**
	 * Form button.
	 *
	 * @access public
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	public function button($text, array $attributes = array()) {
		$attributes = array('type' => 'button') + $attributes;

		return sprintf($this->_tag('button'), $this->_attributes($attributes), $text);
	}

	/**
	 * Filters and cleans each input and applies it to the cleaned array.
	 *
	 * @access public
	 * @param array $inputs
	 * @param boolean $escapeQuotes
	 * @param boolean $removeHtml
	 * @return array
	 */
	public function clean(array $inputs = array(), $escapeQuotes = true, $removeHtml = false) {
		if (!$inputs) {
			$inputs = $this->_data;
		}

		if (is_array($inputs)) {
			foreach ($inputs as $input => $value) {
				if (isset($this->_data[$input])) {
					if ($this->_data[$input] !== '') {
						$this->_cleaned[$input] = self::cleanse($this->_data[$input], $escapeQuotes, $removeHtml);
					} else {
						$this->_cleaned[$input] = '';
					}
				}
			}
		}

		return $this->getCleaned();
	}

	/**
	 * Escapes unwanted characters and tags; options for escaping quotes.
	 *
	 * @access public
	 * @param string $toClean
	 * @param boolean $escapeQuotes
	 * @param boolean $removeHtml
	 * @return string
	 * @static
	 */
	public static function cleanse($toClean, $escapeQuotes = true, $removeHtml = false) {
		if (is_array($toClean)) {
			foreach ($toClean as $key => $value) {
				$toClean[$key] = self::cleanse($value, $escapeQuotes, $removeHtml);
			}
		} else {
			$toClean = trim($toClean);

			if ($removeHtml) {
				$toClean = strip_tags($toClean);
			}

			if ($escapeQuotes) {
				$toClean = htmlentities($toClean, ENT_COMPAT, 'UTF-8');
			}
		}

		return $toClean;
	}

	/**
	 * Close the form.
	 *
	 * @access public
	 * @return string
	 */
	public function close() {
		$output = $this->_tag('form_close');

		if ($this->_config['xhtml'] || $this->_config['legend']) {
			$output = $this->_tag('fieldset_close') . $output;
		}

		return $output;
	}

	/**
	 * Checkbox input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function checkbox($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'checkbox'
		), $attributes);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
	}

	/**
	 * Create the form and the opening tag.
	 *
	 * @access public
	 * @param array $attributes
	 * @return string
	 */
	public function create(array $attributes = array()) {
		$attributes = $attributes + array(
			'id' => $this->model() . 'Form',
			'action' => '',
			'method' => 'post'
		);

		if (isset($attributes['type'])) {
			if ($attributes['type'] === 'file') {
				$attributes['enctype'] = 'multipart/form-data';
			} else if ($attributes['type'] === 'app') {
				$attributes['enctype'] = 'application/x-www-form-urlencoded';
			}
		}

		$legend = isset($attributes['legend']) ? $attributes['legend'] : null;

		unset($attributes['legend'], $attributes['type']);

		// Output
		$output = sprintf($this->_tag('form_open'), $this->_attributes($attributes));

		if ($this->_config['xhtml'] || $legend) {
			$output .= sprintf($this->_tag('fieldset_open'), '');

			if ($legend) {
				$output .= sprintf($this->_tag('legend'), '', $legend);
				$this->_config['legend'] = true;
			}
		}

		return $output;
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
		$this->_errors[$input] = $message;
	}

	/**
	 * File input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function file($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'file'
		), $attributes);

		unset($attributes['value']);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
	}

	/**
	 * Resets all values.
	 *
	 * @access public
	 * @return void
	 */
	public function flush() {
		$this->_data = array();
		$this->_errors = array();
		$this->_cleaned = array();
	}

	/**
	 * Returns a value from the post.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key = null) {
		if (isset($this->_data[$key])) {
			return $this->_data[$key];

		} else if (empty($key) && !empty($this->_data)) {
			return $this->_data;
		}

		return null;
	}

	/**
	 * Returns all the error messages.
	 *
	 * @access public
	 * @return array
	 */
	public function getErrors() {
		return $this->_errors;
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
		return (!empty($this->_errors) && isset($this->_errors[$input])) ? $class : '';
	}

	/**
	 * Return the array of all cleaned elements.
	 *
	 * @access public
	 * @return string
	 */
	public function getCleaned() {
		return $this->_cleaned;
	}

	/**
	 * Hidden input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function hidden($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'hidden'
		), $attributes);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
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
	 * Image input field.
	 *
	 * @access public
	 * @param string $title
	 * @param array $attributes
	 * @return string
	 */
	public function image($title, array $attributes = array()) {
		$attributes = $attributes + array(
			'id' => $this->model() . 'ImageButton',
			'alt' => $title,
			'type' => 'image',
			'src' => ''
		);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
	}

	/**
	 * Form input label.
	 *
	 * @access public
	 * @param string $name
	 * @param string $title
	 * @param array $attributes
	 * @return string
	 */
	public function label($name, $title, array $attributes = array()) {
		$attributes = $attributes + array(
			'for' => $this->model() . $this->inflect($name)
		);

		return sprintf($this->_tag('label'), $this->_attributes($attributes), $title);
	}

	/**
	 * Return the model name.
	 *
	 * @access public
	 * @return string
	 */
	public function model() {
		return $this->_config['model'];
	}

	/**
	 * Password input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function password($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'password'
		), $attributes);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
	}

	/**
	 * Checks to see if the post is submitted; also saves the post property.
	 *
	 * @access public
	 * @param array $post 		- The $_POST or $_GET
	 * @param string $submit 	- Name of the submit button (optional)
	 * @return boolean
	 */
	public function process(array $post = array(), $submit = null) {
		$model = $this->model();

		if (!$post) {
			$post = $_POST;
		}

		if ($_FILES) {
			$post = array_merge_recursive($post, $this->_files());
		}

		$this->_data = isset($post[$model]) ? $post[$model] : array();

		return ((!empty($submit) && isset($this->_data[$submit])) || (empty($submit) && !empty($this->_data)));
	}

	/**
	 * Radio input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function radio($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'radio'
		), $attributes);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
	}

	/**
	 * Form reset button.
	 *
	 * @access public
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	public function reset($text = 'Reset', array $attributes = array()) {
		$attributes = $attributes + array(
			'id' => $this->model() . 'ResetButton',
			'type' => 'reset'
		);

		return sprintf($this->_tag('button'), $this->_attributes($attributes), $text);
	}

	/**
	 * Select field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $options
	 * @param array $attributes
	 * @return string
	 */
	public function select($name, $options, array $attributes = array()) {
		$keys = array_keys($options);
		$selected = isset($attributes['default']) ? $attributes['default'] : $keys[0];

		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'select'
		), $attributes);

		if ($attributes['value'] !== '' && $attributes['value'] !== null) {
			$selected = $attributes['value'];
		}

		unset($attributes['type'], $attributes['value']);

		return sprintf($this->_tag('select'), $this->_attributes($attributes), $this->_options($options, $selected));
	}

	/**
	 * Form submit button.
	 *
	 * @access public
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	public function submit($text = 'Submit', array $attributes = array()) {
		$attributes = $attributes + array(
			'id' => $this->model() . 'SubmitButton',
			'type' => 'submit'
		);

		return sprintf($this->_tag('button'), $this->_attributes($attributes), $text);
	}

	/**
	 * Text input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function text($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'text'
		), $attributes);

		return sprintf($this->_tag('input'), $this->_attributes($attributes));
	}

	/**
	 * Textarea input field.
	 *
	 * @access public
	 * @param string $name
	 * @param array $attributes
	 * @return string
	 */
	public function textarea($name, array $attributes = array()) {
		$attributes = $this->_input(array(
			'name' => $name,
			'type' => 'textarea',
			'cols' => 30,
			'rows' => 5
		), $attributes);

		$value = $attributes['value'];
		unset($attributes['value'], $attributes['type']);

		return sprintf($this->_tag('textarea'), $this->_attributes($attributes), $value);
	}

	/**
	 * Checks to see if there are no errors and validates.
	 *
	 * @access public
	 * @param array $schema
	 * @return boolean
	 */
	public function validates(array $schema = array()) {
		if ($schema) {
			foreach ($schema as $input => $validations) {
				$required = isset($validations['required']) ? $validations['required'] : true;
				unset($validations['required']);

				$this->_validate($input, $validations, $required);
			}
		}

		return (count($this->_errors) === 0);
	}

	/**
	 * If an input is set with a value keep it, or display default.
	 *
	 * @access public
	 * @param string $type		- The type of form element to check against
	 * @param string $field		- The input value to check against
	 * @param string $value		- The value your are submitting
	 * @param string $default	- Default value to display
	 * @return mixed
	 */
	public function value($type, $field, $value = '', $default = '') {
		$input = isset($this->_data[$field]) ? $this->_data[$field] : null;
		$output = '';

		switch ($type) {
			default:
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
			case 'file':
				if ($input !== null) {
					$output = self::cleanse($input);
				} else {
					$output = ($default !== null) ? $default : '';
				}
			break;
			case 'radio':
				if ($input !== null) {
					$output = ($input == $value);
				} else {
					$output = ($value == $default || $default === true);
				}
			break;
			case 'select':
				$output = ($input !== null) ? $input : $default;
			break;
			case 'checkbox':
				$output = false;

				if (is_array($input)) {
					$flipped = array_flip($input);
					$output = isset($flipped[$value]);
				} else {
					if ($input !== null) {
						$output = ($input == $value);
					} else {
						$output = ($value == $default || $default === true);
					}
				}
			break;
		}

		return $output;
	}

	/**
	 * Format the attributes.
	 *
	 * @access protected
	 * @param array $attributes
	 * @return string
	 */
	protected function _attributes(array $attributes) {
		$clean = array();

		if ($attributes) {
			foreach ($attributes as $att => $value) {
				if ($att !== 'value') {
					$value = self::cleanse($value, true, true);
				}

				$clean[] = $att . '="' . $value . '"';
			}
		}

		return ' ' . implode(' ', $clean);
	}

	/**
	 * Executes the validation method.
	 *
	 * @access protected
	 * @param string $input
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @throws Exception
	 */
	protected function _execute($input, $method, $args) {
		$arguments = array();
		$arguments[] = isset($this->_data[$input]) ? $this->_data[$input] : null;
		$message = '';

		if (is_array($args)) {
			foreach ($args as $index => $value) {
				if ($index == 0) {
					$message = $value;
				} else {
					$arguments[] = $value;
				}
			}
		}

		if (!call_user_func_array(array(__NAMESPACE__ . '\Formation', $method), $arguments)) {
			if ($message) {
				$this->error($input, $message);
			} else {
				throw new Exception(sprintf('The method "%s" to validation "%s" failed to execute.', $method, $input));
			}

			return false;
		}

		return true;
	}

	/**
	 * Reformat the $_FILES array.
	 *
	 * @access protected
	 * @return array
	 */
	protected function _files() {
		$clean = array();

		foreach ($_FILES as $model => $data) {
			if (!isset($clean[$model])) {
				$clean[$model] = array();
			}

			foreach ($data as $key => $values) {
				foreach ($values as $field => $value) {
					if (!isset($clean[$model][$field])) {
						$clean[$model][$field] = array();
					}

					$clean[$model][$field][$key] = $value;
				}
			}
		}

		return $clean;
	}

	/**
	 * Process and prepare all fields with default data.
	 *
	 * @access protected
	 * @param array $params
	 * @param array $attributes
	 * @return array
	 * @throws Exception
	 */
	protected function _input($params, array $attributes = array()) {
		$attributes = $attributes + $params;
		$attributes['name'] = $this->model() . '[' . $attributes['name'] . ']';

		if (!isset($attributes['id'])) {
			$attributes['id'] = $this->model() . $this->inflect($params['name']);
		}

		if ($params['type'] == 'radio') {
			$attributes['id'] .= $this->inflect($attributes['value']);
		}

		// Defaults and value
		$default = isset($attributes['default']) ? $attributes['default'] : null;
		$value = isset($attributes['value']) ? $attributes['value'] : '';

		if (($params['type'] === 'checkbox' || $params['type'] === 'radio') && $value === '') {
			throw new Exception('When using the checkbox or radio input type, the value option is required.');
		}

		$result = $this->value($params['type'], $params['name'], $value, $default);

		if ($result === true) {
			$attributes['checked'] = 'checked';
		} else if ($params['type'] !== 'radio' && $params['type'] !== 'checkbox') {
			$attributes['value'] = $result;
		} else {
			$attributes['value'] = $value;
		}

		// Input states
		foreach (array('disabled', 'readonly', 'multiple') as $attr) {
			if (isset($attributes[$attr])) {
				if (($attributes[$attr] === true) || ($attributes[$attr] == $attr)) {
					$attributes[$attr] = $attr;

					if ($attr === 'multiple') {
						if ($params['type'] === 'checkbox') {
							$attributes['name'] .= '[]';
							$attributes['id'] .= $this->inflect($attributes['value']);
							unset($attributes['multiple']);

						} else if ($params['type'] === 'select') {
							$attributes['name'] .= '[]';
						}
					}
				} else {
					unset($attributes[$attr]);
				}
			}
		}

		// Apply error and classes
		if ($class = $this->getClass($params['name'])) {
			if (isset($attributes['class'])) {
				$attributes['class'] = $class . ' ' . $attributes['class'];
			} else {
				$attributes['class'] = $class;
			}
		}

		// Unset
		unset($attributes['default']);

		return $attributes;
	}

	/**
	 * Form select options.
	 *
	 * @access protected
	 * @param array $options
	 * @param string $selected
	 * @return string
	 */
	protected function _options($options, $selected) {
		$output = '';

		if ($options) {
			foreach ($options as $value => $label) {
				if (is_array($label)) {
					$output .= sprintf($this->_tag('optgroup_open'), $this->_attributes(array('label' => $value)));
					$output .= $this->_options($label, $selected);
					$output .= $this->_tag('optgroup_close');

				} else {
					if (!is_array($selected)) {
						$selected = array($selected);
					}

					$attributes = array('value' => $value);
					$flipped = array_flip($selected);

					if (isset($flipped[$value])) {
						$attributes['selected'] = 'selected';
					}

					$output .= sprintf($this->_tag('option'), $this->_attributes($attributes), $label);
				}
			}
		}

		return $output;
	}

	/**
	 * Determine the tag to use.
	 *
	 * @access protected
	 * @param string $tag
	 * @return string
	 */
	protected function _tag($tag) {
		if (is_array($this->_tags[$tag])) {
			return ($this->_config['xhtml']) ? $this->_tags[$tag][1] : $this->_tags[$tag][0];
		}

		return $this->_tags[$tag];
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
	protected function _validate($input, $cleaners, $required = true) {
		$validate = ($required === true);

		if (isset($this->_data[$input]) && is_array($this->_data[$input])) {
			if ($required === false && isset($this->_data[$input]['tmp_name']) && !empty($this->_data[$input]['tmp_name'])) {
				$validate = true;
			}
		} else if ($required === false && !empty($this->_data[$input])) {
			$validate = true;
		}

		if ($validate) {
			foreach ($cleaners as $method => $args) {
				if (!is_array($args) && !empty($args)) {
					$args = array($args);
				}

				if (!$this->_execute($input, $method, $args)) {
					return false;
				}
			}
		}

		return true;
	}

}