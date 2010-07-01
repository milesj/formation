<?php
/**
 * Formation - Builder and Validator
 *
 * A form validation and builder class that can build form elements and then validates form inputs for errors and returns a cleaned result.
 * 
 * @author      Miles Johnson - www.milesj.me
 * @copyright   Copyright 2006-2010, Miles Johnson, Inc.
 * @license     http://www.opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link        http://milesj.me/resources/script/form-manager
 */
 
class Form { 

	/**
	 * Current version: http://milesj.me/resources/logs/form-manager
	 *
	 * @access public
	 * @var int
	 */
	public $version = '2.5';

	/**
	 * The current doctype.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_doctype = 'html';

    /**
	 * The current model.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_model;

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
		'label'			=> '<label%s>%s</label>',
        'button'        => '<button%s>%s</button>'
	);
	
	/**
	 * Initialize the class.
	 *
	 * @access public
     * @param string $model
	 * @param boolean $xhtml
	 * @return void
	 */
	public function __construct($model, $xhtml = false) {
		$this->flush();

        if (empty($model)) {
			$model = 'Form';
		}

		$this->_model = $this->inflect($model);

		if ($xhtml) {
			$this->_doctype = 'xhtml';
		}
	}

	/**
	 * Form button.
	 *
	 * @access public
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	public function button($text, $attributes = array()) {
		$attributes += array('type' => 'button');

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
	public function clean($inputs = '', $escapeQuotes = true, $removeHtml = false) {
		if (empty($inputs)) {
			$inputs = $this->__post;
		}

		if (is_array($inputs)) {
			foreach ($inputs as $input => $value) {
				if (array_key_exists($input, $this->__post)) {
					if ($this->__post[$input] != '') {
						$this->__cleaned[$input] = $this->cleanse($this->__post[$input], $escapeQuotes, $removeHtml);
					} else {
						$this->__cleaned[$input] = '';
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

		if ($this->_doctype == 'xhtml') {
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
    public function checkbox($name, $attributes = array()) {
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
	public function create($attributes = array()) {
		$attributes = $attributes + array(
			'id' => $this->_model .'Form',
			'action' => '',
			'method' => 'post'
		);

		if (isset($attributes['type'])) {
			if ($attributes['type'] == 'file') {
				$attributes['enctype'] = 'multipart/form-data';
			} else if ($attributes['type'] == 'app') {
				$attributes['enctype'] = 'application/x-www-form-urlencoded';
			}
		}

        $legend = isset($attributes['legend']) ? $attributes['legend'] : null;

        unset($attributes['legend'], $attributes['type']);

        // Output
		$output = sprintf($this->_tag('form_open'), $this->_attributes($attributes));
        
		if ($this->_doctype == 'xhtml' || $legend) {
			$output .= sprintf($this->_tag('fieldset_open'), '');
			
			if ($legend) {
				$output .= sprintf($this->_tag('legend'), '', $legend);
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
		$this->__errors[$input] = $message;
		return false;
	}

    /**
     * File input field.
     *
     * @access public
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public function file($name, $attributes = array()) {
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
		$this->__post = array();
		$this->__errors = array();
		$this->__cleaned = array();
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
		} else if (empty($key) && !empty($this->__post)) {
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
			return in_array($input, array_keys($this->__errors)) ? $class : '';
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
     * Hidden input field.
     *
     * @access public
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public function hidden($name, $attributes = array()) {
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
	 * Form input label.
	 *
	 * @access public
	 * @param string $name
	 * @param string $title
	 * @param string $attributes
	 * @return string
	 */
	public function label($name, $title, $attributes = array()) {
		$attributes = $attributes + array('for' => $this->_model . $this->inflect($name));
		
		return sprintf($this->_tag('label'), $this->_attributes($attributes), $title);
	}

    /**
     * Password input field.
     *
     * @access public
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public function password($name, $attributes = array()) {
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
	public function process($post, $submit = null) {
		$this->__post = isset($post[$this->_model]) ? $post[$this->_model] : array();

		if ((!empty($submit) && isset($this->__post[$submit])) || (empty($submit) && !empty($this->__post))) {
			return true;
		} else {
			$this->__post = array();
			return false;
		}
	}

    /**
     * Radio input field.
     *
     * @access public
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public function radio($name, $attributes = array()) {
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
	public function reset($text = 'Reset', $attributes = array()) {
		$attributes = $attributes + array(
			'id' 	=> $this->_model .'ResetButton',
			'type' 	=> 'reset'
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
    public function select($name, $options, $attributes = array()) {
        $selected = isset($attributes['default']) ? $attributes['default'] : null;

        $attributes = $this->_input(array(
            'name' => $name,
            'type' => 'select'
        ), $attributes);

        if (!empty($attributes['value'])) {
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
	public function submit($text = 'Submit', $attributes = array()) {
		$attributes = $attributes + array(
			'id' 	=> $this->_model .'SubmitButton',
			'type' 	=> 'submit'
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
    public function text($name, $attributes = array()) {
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
    public function textarea($name, $attributes = array()) {
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
	public function validates($schema = array()) {
		if (!empty($schema)) {
			foreach ($schema as $input => $validations) {
				$required = isset($validations['required']) ? $validations['required'] : true;
				unset($validations['required']);

				$this->_validate($input, $validations, $required);
			}
		}

		return (count($this->__errors) === 0);
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
		$input = isset($this->__post[$input]) ? $this->__post[$input] : null;
		$output = '';

		switch ($type) {
			default:
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
			case 'file':
				if (!empty($input)) {
					$output = self::cleanse($input);
				} else {
				 	$output = isset($default) ? $default : '';
				}
			break;
			case 'radio':
				if (!empty($input)) {
					$output = ($input == $value);
				} else {
					$output = ($value == $default || $default === true);
				}
			break;
            case 'select':
                $output = !empty($input) ? $input : $default;
            break;
			case 'checkbox':
				$output = false;

				if (is_array($input)) {
					$output = in_array($value, $input);
				} else {
					if ($input == $value) {
						$output = true;
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
	protected function _attributes($attributes) {
		$clean = array();

		if (!empty($attributes)) {
			foreach ($attributes as $att => $value) {
				if ($att != 'value') {
					$value = self::cleanse($value, true, true);
				}

				$clean[] = $att .'="'. $value .'"';
			}
		}

		return ' '. implode(' ', $clean);
	}

	/**
	 * Executes the validation method.
	 *
	 * @access protected
	 * @param string $input
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	protected function _execute($input, $method, $args) {
		$arguments = array();
		$arguments[] = isset($this->__post[$input]) ? $this->__post[$input] : null;

		if (is_array($args)) {
			foreach ($args as $index => $value) {
				$arguments[] = $value;
			}
		}

		if (!call_user_func_array(array('Formation', $method), $arguments)) {
			if (!empty($arguments[1])) {
				return $this->error($input, $arguments[1]);
			} else {
				trigger_error('Formation::_execute(): The method "'. $method .'" to validate "'. $input .'" failed to execute.', E_USER_WARNING);
				return false;
			}
		}

		return true;
	}

    /**
     * Process and prepare all fields with default data.
     *
     * @access protected
     * @param array $params
     * @param array $attributes
     * @return array
     */
    protected function _input($params, $attributes = array()) {
        $attributes = $attributes + $params;
        $attributes['name'] = $this->_model .'['. $attributes['name'] .']';

        if (!isset($attributes['id'])) {
            $attributes['id'] = $this->_model . $this->inflect($params['name']);
        }

        if ($params['type'] == 'radio') {
            $attributes['id'] .= $this->inflect($attributes['value']);
        }

        // Defaults and value
        $default = isset($attributes['default']) ? $attributes['default'] : null;
        $value = isset($attributes['value']) ? $attributes['value'] : '';

        if (($params['type'] == 'checkbox' || $params['type'] == 'radio') && !$value) {
			trigger_error('Form::_input(): When using the checkbox or radio input type, the value option is required.', E_USER_WARNING);
		}

        $result = $this->value($params['type'], $params['name'], $value, $default);

        if ($result === true) {
            $attributes['checked'] = 'checked';
        } else if (!empty($result)) {
            $attributes['value'] = $result;
        } else {
            $attributes['value'] = $value;
        }

        // Input states
        foreach (array('disabled', 'readonly', 'multiple') as $attr) {
            if (isset($attributes[$attr])) {
                if (($attributes[$attr] === true) || ($attributes[$attr] == $attr)) {
                    $attributes[$attr] = $attr;

                    if ($attr == 'multiple') {
                        if ($params['type'] == 'checkbox') {
                            $attributes['name'] .= '[]';
                            $attributes['id'] .= $this->inflect($attributes['value']);
                            unset($attributes['multiple']);

                        } else if ($params['type'] == 'select') {
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
				$attributes['class'] = $class .' '. $attributes['class'];
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
	 * @param string $options
     * @param string $selected
	 * @return string
	 */
	protected function _options($options, $selected) {
        if (!empty($options)) {
            $output = '';

            foreach ($options as $value => $label) {
                if (is_array($label)) {
                    $output .= sprintf($this->_tag('optgroup_open'), $this->_attributes(array('label' => $value)));
                    $output .= $this->_options($label, $selected);
                    $output .= $this->_tag('optgroup_close');

                } else {
                    $attributes = array('value' => $value);

                    if (($value == $selected) || (is_array($selected) && in_array($value, $selected))) {
                        $attributes['selected'] = 'selected';
                    }

                    $output .= sprintf($this->_tag('option'), $this->_attributes($attributes), $label);
                }
            }

            return $output;
        }
	}

    /**
     * Determine the tag to use.
     *
     * @access protected
     * @param string $tag
     * @return string
     */
    protected function _tag($tag) {
        if (is_array($this->__tags[$tag])) {
            $tag = ($this->_doctype === 'html') ? $this->__tags[$tag][0] : $this->__tags[$tag][1];
        } else {
            $tag = $this->__tags[$tag];
        }

        return $tag;
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
	 	if (($required === true) || ($required === false && !empty($this->__post[$input]))) {
			foreach ($cleaners as $method => $args) {
				if (!in_array($method, get_class_methods('Formation'))) {
					trigger_error('Formation::_validate(): Cleaning method "'. $method .'" does not exist.', E_USER_WARNING);

				} else if (!is_array($args) && !empty($args)) {
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

class Formation {

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

		return !preg_match($expression, $input) ? false : $input;
	}

	/**
	 * Escapes characters that would break the regex.
	 *
	 * @access public
	 * @param array $characters
	 * @return string
	 */
	public static function escape($characters) {
		return preg_quote(implode('', $characters), '/');
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

        return !preg_match('/^[a-zA-Z0-9\s'. $exceptions .']+$/', $input) ? false : $input;
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
	public static function isAlnum($input, $message, $exceptions = array()) {
		if (is_array($exceptions)) {
			$exceptions = self::escape($exceptions);
		}
		
		return !preg_match('/^[a-zA-Z0-9\s'. $exceptions .']+$/', $input) ? false : $input;
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
	public static function isAlpha($input, $message, $exceptions = array()) {
		if (is_array($exceptions)) {
			$exceptions = self::escape($exceptions);
		}
		
		return !preg_match('/^[a-zA-Z\s'. $exceptions .']+$/', $input) ? false : $input;
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
        
		return !in_array($input, $types, true) ? false : $input;
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
		return !preg_match('/^[-]*[0-9][0-9]*\.[0-9]{'. intval($decimals) .'}$/', $input) ? false : $input;
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
		return !preg_match('/^[\+0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i', mb_strtolower($input)) ? false : $input;
	}
	
	/**
     * Checks to see if a value has a correct extension.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param array $extensions
     * @return mixed
     * @static
     */
	public static function isExt($input, $message, $extensions = array()) {
		if (!is_array($extensions)) {
			$extensions = array('gif', 'jpeg', 'png', 'jpg');
		}
        
		$ext = mb_strtolower(trim(mb_strrchr($input, '.'), '.'));

		return !in_array($ext, $extensions, true) ? false : $input;
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
		return !preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $input) ? false : $input;
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
	public static function isNumeric($input, $message, $exceptions = array()) {
		if (is_array($exceptions)) {
			$exceptions = self::escape($exceptions);
		}
		
		return !preg_match('/^[0-9\s'. $exceptions .']+$/', $input) ? false : $input;
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
		return !preg_match('/^\([0-9]{3}\)\s[0-9]{3}[-]?[0-9]{4}$/', $input) ? false : $input;
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
		return !preg_match('/^(?:0?[1-9]|1[0-2]):(?:[0-5][0-9])(?::[0-5][0-9])? [PA]\.?M\.?$/', mb_strtoupper($input)) ? false : $input;
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
		return !preg_match('/^(?:(?:http|ftp)s?):\/\/(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,4}(?:[-a-zA-Z0-9._\/&=+%?]+)?$/', mb_strtolower($input)) ? false : $input;
	}
	
	/**
     * Checks to see if an item is within an array/list.
	 *
	 * @access public
     * @param string $input
	 * @param string $message
	 * @param array $list
     * @return mixed
     * @static
     */
	public static function inList($input, $message, $list = array()) {
		if (!is_array($list)) {
			return false;
		}

		return !in_array($input, $list, true) ? false : $input;
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
	
}
