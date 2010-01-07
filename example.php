<?php

// Turn on error reporting
error_reporting(E_ALL);

// Include and initialize
require_once('form.php'); 
$form = new Form();

// Check to see if form is posted
if ($form->process($_POST)) {
	$schema = array(
		'name' => array(
			'notEmpty' => 'Your name is required',
			'isAllChars' => 'Your name contains invalid characters'
		),
		'email' => array(
			'notEmpty' => 'Your email is required',
			'isEmail' => 'Your email is invalid'
		),
		'website' => array(
			'isWebsite' => 'Your website URL is invalid',
			'required' => false
		),
		'password' => array(
			'notEmpty' => 'Your password is required',
			'isAlnum' => 'Your password may only be alpha-numeric',
			'checkLength' => array('Password must be between 6-12 characters', 12, 6)
		),
		'age' => array(
			'isNumeric' => 'Your age must be numeric',
			'required' => false
		),
		'tos' => array(
			'notEmpty' => 'You must agree to the TOS'
		)
	);
	
	// Validate form and pass
	if ($form->validates($schema)) {
		$clean = $form->clean();
		// Data is ready for use
	}
	
	$errors = $form->getErrors();
} ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Formation Test</title>
<style type='text/css'>
.input-error { border: 1px solid red; }
</style>
</head>

<body>
<?php // Form errors
if (!empty($errors)) { ?>
<ul>
	<?php foreach ($errors as $error) { ?>
	<li><?php echo $error; ?></li>
	<?php } ?>
</ul>
<?php } ?>

<?php // Create form
echo $form->create('Login'); ?>

<p><?php echo $form->label('name', 'Name'); ?><br />
<?php echo $form->input('name'); ?></p>

<p><?php echo $form->label('email', 'Email'); ?><br />
<?php echo $form->input('email'); ?></p>

<p><?php echo $form->label('password', 'Password'); ?><br />
<?php echo $form->input('password', array('type' => 'password')); ?></p>

<p><?php echo $form->label('website', 'Website'); ?><br />
<?php echo $form->input('website'); ?></p>

<p><?php echo $form->label('age', 'Age'); ?><br />
<?php echo $form->input('age', array('size' => 1)); ?></p>

<p><?php echo $form->input('tos', array('type' => 'checkbox', 'value' => 'yes')); ?>
<?php echo $form->label('tos', 'Do you agree to the Terms of Service?'); ?></p>

<p>
    <?php echo $form->input('gender', array('type' => 'radio', 'value' => 'male', 'id' => 'male', 'default' => true)); ?> Male
    <?php echo $form->input('gender', array('type' => 'radio', 'value' => 'female', 'id' => 'female')); ?> Female
</p>	

<p><?php echo $form->submit('Submit'); ?>
<?php echo $form->reset('Reset', array('class' => 'button'))?></p>

<?php // Close form
echo $form->close(); ?>

</body>
</html>
