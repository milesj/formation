<?php

// Turn on error reporting
error_reporting(E_ALL);

function debug($var) {
    echo '<pre>'. print_r($var, true) .'</pre>';
}

// Include and initialize
require_once('form.php');

$form = new Form('User', true);

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
echo $form->create(array('type' => 'file', 'legend' => 'Formation')); ?>

<p><?php echo $form->label('name', 'Name:'); ?><br />
<?php echo $form->text('name'); ?></p>

<p><?php echo $form->label('email', 'Email:'); ?><br />
<?php echo $form->text('email'); ?></p>

<p><?php echo $form->label('password', 'Password:'); ?><br />
<?php echo $form->password('password'); ?></p>

<p><?php echo $form->label('about', 'About Me:'); ?><br />
<?php echo $form->textarea('about'); ?></p>

<p><?php echo $form->label('website', 'Website:'); ?><br />
<?php echo $form->text('website'); ?></p>

<p><?php echo $form->label('photo', 'Photo:'); ?><br />
<?php echo $form->file('photo'); ?></p>

<p><?php echo $form->label('gender', 'Gender:'); ?><br />
<?php echo $form->select('gender', array('male' => 'Male', 'female' => 'Female'), array('multiple' => true)); ?></p>

<p><?php echo $form->label('age', 'Age:'); ?><br />
<?php echo $form->text('age', array('size' => 1)); ?></p>

<p><?php echo $form->checkbox('tos', array('value' => 'yes')); ?>
<?php echo $form->label('tos', 'Do you agree to the Terms of Service?'); ?></p>

<p>
    <?php echo $form->label('color', 'Color:'); ?><br />
    <?php foreach (array('red', 'green', 'blue', 'yellow', 'orange') as $color) {
        echo $form->checkbox('color', array('value' => $color, 'default' => 'green', 'multiple' => true)); ?> <?php echo ucfirst($color);
    } ?>
</p>	

<p><?php echo $form->submit('Submit'); ?>
<?php echo $form->reset('Reset', array('class' => 'button'))?></p>

<?php // Close form
echo $form->close(); ?>

</body>
</html>
