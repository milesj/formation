<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/formation
 */

// Turn on error reporting
error_reporting(E_ALL);

function debug($var) {
	echo '<pre>' . print_r($var, true) . '</pre>';
}

// Include and initialize
include_once '../Form.php';

$form = new \mjohnson\formation\Form('User');
$options = array(
	'red' => 'Red', 'green' => 'Green', 'blue' => 'Blue', 'yellow' => 'Yellow',
	'orange' => 'Orange', 'black' => 'Black', 'white' => 'White'
);

// Check to see if form is posted
if ($form->process()) {
	$schema = array(
		'text1' => array(
			'notEmpty' => 'Text 1 is required',
			'isAllChars' => 'Text 1 contains invalid characters'
		),
		'text2' => array(
			'notEmpty' => 'Text 2 is required',
			'isAlpha' => 'Text 2 may only contain letters'
		),
		'email' => array(
			'notEmpty' => 'Email is required',
			'isEmail' => 'Email is invalid'
		),
		'website' => array(
			'isWebsite' => 'Website URL is invalid',
			'required' => false
		),
		'password' => array(
			'notEmpty' => 'Password is required',
			'isAlnum' => 'Password may only be alpha-numeric',
			'checkLength' => array('Password must be between 6-12 characters', 12, 6)
		),
		'decimal' => array(
			'isDecimal' => 'Decimal must be 2 points',
			'required' => false
		),
		'date' => array(
			'notEmpty' => 'Date is required',
			'isDate' => 'Date is invalid: mm/dd/yyyy'
		),
		'checkbox1' => array(
			'notEmpty' => 'Single checkbox is required'
		),
		'file1' => array(
			'isFile' => 'File 1 is required',
			'maxFilesize' => 'Filesize is too large'
		),
		'file2' => array(
			'required' => false,
			'isExt' => 'Invalid extension',
			'maxWidth' => array('Image width is too large', 100)
		)
	);

	// Validate form and pass
	if ($form->validates($schema)) {
		debug($form->clean());
	}
} ?>

<!DOCTYPE html>
<html>
<head>
<title>Formation</title>
<style>
	body { padding: 15px; font: normal 12px Arial, Tahoma, sans-serif; color: #000; }
	.input-error { border: 1px solid red; }
</style>
</head>
<body>

	<?php // Form errors
	if ($errors = $form->getErrors()) { ?>
		<ul>
			<?php foreach ($errors as $error) { ?>
				<li><?php echo $error; ?></li>
			<?php } ?>
		</ul>
	<?php }

	// Create form
	echo $form->create(array('type' => 'file', 'legend' => 'Formation'));
	echo $form->hidden('hidden', array('value' => 'Hidden value')); ?>

	<p><?php echo $form->label('text1', 'Text 1: Required, Alpha-numeric, Punctuation'); ?><br>
	<?php echo $form->text('text1'); ?></p>

	<p><?php echo $form->label('text2', 'Text 2: Required, Alpha'); ?><br>
	<?php echo $form->text('text2'); ?></p>

	<p><?php echo $form->label('email', 'Email: Required, Email'); ?><br>
	<?php echo $form->text('email'); ?></p>

	<p><?php echo $form->label('website', 'Website: Optional'); ?><br>
	<?php echo $form->text('website'); ?></p>

	<p><?php echo $form->label('password', 'Password: Required, Alpha-numeric, Within range'); ?><br>
	<?php echo $form->password('password'); ?></p>

	<p><?php echo $form->label('decimal', 'Decimal: Optional, 2 points'); ?><br>
	<?php echo $form->text('decimal'); ?></p>

	<p><?php echo $form->label('date', 'Date: Required, mm/dd/yyyy'); ?><br>
	<?php echo $form->text('date'); ?></p>

	<p><?php echo $form->label('file1', 'File 1: Required, Filesize 5MB'); ?><br>
	<?php echo $form->file('file1'); ?></p>

	<p><?php echo $form->label('file2', 'File 2: Optional, Max width 100px'); ?><br>
	<?php echo $form->file('file2'); ?></p>

	<p><?php echo $form->label('select1', 'Select:'); ?><br>
	<?php echo $form->select('select1', $options, array('default' => 'green')); ?></p>

	<p><?php echo $form->label('select2', 'Select: Multiple'); ?><br>
	<?php echo $form->select('select2', $options, array('default' => 'blue', 'multiple' => true)); ?></p>

	<p><?php echo $form->label('checkbox1', 'Checkbox: Single, Required'); ?><br>
	<?php echo $form->checkbox('checkbox1', array('value' => 1)); ?></p>

	<p>
		<?php echo $form->label('checkbox2', 'Checkbox: Multiple'); ?><br>
		<?php foreach ($options as $key => $value) {
			echo $form->checkbox('checkbox2', array('value' => $key, 'multiple' => true)) .' '. $value;
		} ?>
	</p>

	<p>
		<?php echo $form->label('radio', 'Radio:'); ?><br>
		<?php foreach ($options as $key => $value) {
			echo $form->radio('radio', array('value' => $key, 'default' => 'black')) .' '. $value;
		} ?>
	</p>

	<p>
		<?php echo $form->submit('Submit'); ?>
		<?php echo $form->reset('Reset')?>
		<?php echo $form->button('Button', array('class' => 'button')); ?>
	</p>

	<?php // Close form
	echo $form->close(); ?>

</body>
</html>
