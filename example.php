<?php

// Turn on error reporting
error_reporting(E_ALL);

function debug($var) {
    echo '<pre>'. print_r($var, true) .'</pre>';
}

// Include and initialize
require_once('form.php');

$form = new Form('User');
$options = array(
    'red' => 'Red', 'green' => 'Green', 'blue' => 'Blue',
    'yellow' => 'Yellow', 'orange' => 'Orange', 'black' => 'Black', 'white' => 'White'
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
        'file' => array(
            'isFile' => 'File is required'
        )
	);
	
	// Validate form and pass
	if ($form->validates($schema)) {
		debug($form->clean());
	}
	
	$errors = $form->getErrors();
} ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Formation Test</title>
<style type="text/css">
    body { padding: 15px; font: normal 12px Arial, Tahoma, sans-serif; color: #000; }
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
    <?php }

    // Create form
    echo $form->create(array('type' => 'file', 'legend' => 'Formation'));
    echo $form->hidden('hidden', array('value' => 'Hidden value')); ?>

    <p><?php echo $form->label('text1', 'Text 1: Required, Alpha-numeric, Punctuation'); ?><br />
    <?php echo $form->text('text1'); ?></p>

    <p><?php echo $form->label('text2', 'Text 2: Required, Alpha'); ?><br />
    <?php echo $form->text('text2'); ?></p>

    <p><?php echo $form->label('email', 'Email: Required, Email'); ?><br />
    <?php echo $form->text('email'); ?></p>

    <p><?php echo $form->label('website', 'Website: Optional'); ?><br />
    <?php echo $form->text('website'); ?></p>

    <p><?php echo $form->label('password', 'Password: Required, Alpha-numeric, Within range'); ?><br />
    <?php echo $form->password('password'); ?></p>

    <p><?php echo $form->label('decimal', 'Decimal: Optional, 2 points'); ?><br />
    <?php echo $form->text('decimal'); ?></p>

    <p><?php echo $form->label('date', 'Date: Required, mm/dd/yyyy'); ?><br />
    <?php echo $form->text('date'); ?></p>

    <p><?php echo $form->label('file', 'File: Required'); ?><br />
    <?php echo $form->file('file'); ?></p>

    <p><?php echo $form->label('select', 'Select:'); ?><br />
    <?php echo $form->select('select', $options, array('default' => 'blue')); ?></p>

    <p><?php echo $form->label('checkbox1', 'Checkbox: Single, Required'); ?><br />
    <?php echo $form->checkbox('checkbox1', array('value' => 1)); ?></p>

    <p>
        <?php echo $form->label('checkbox2', 'Checkbox: Multiple'); ?><br />
        <?php foreach ($options as $key => $value) {
            echo $form->checkbox('checkbox2', array('value' => $key, 'multiple' => true)) .' '. $value;
        } ?>
    </p>

    <p>
        <?php echo $form->label('radio', 'Radio:'); ?><br />
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
