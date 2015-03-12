# Formation #

*Documentation may be outdated or incomplete as some URLs may no longer exist.*

*Warning! This codebase is deprecated and will no longer receive support; excluding critical issues.*

Formation is a lightweight class that can build all necessary elements for a form, process the posted data (post, get, request), validate the data based on a schema and finally return a cleaned result. Has logic for error handling and applying a class to invalid fields. Additionally, all validator functions are static and can be used externally.

Formation is a play on words for: Form + Formation + Creation + Validation.

### Class Features: ###

* Create a form and all its field elements
* Manage all labels and fields with the correct IDs and associations
* Determines to correct tags for HTML or XHTML
* Supports all input types: text, textarea, password, hidden, file, radio, checkbox, select, image
* Has a large library of validators: alpha, numeric, date, time, ip, email, url, and many more
* Validates certain elements based on the supplied schema
* Cleans all data after a successful validation
* Can set certain fields as required or optional

## Installation ##

Install by manually downloading the library or defining a [Composer dependency](http://getcomposer.org/).

```javascript
{
    "require": {
        "mjohnson/formation": "4.0.0"
    }
}
```

## Creating The Form ##

Before we begin creating the form, I will briefly go over how the attributes system work. Most, if not all the methods in `Formation` take an argument called `$arguments`, which will be equivalent to the HTML elements attributes. For example, if you pass a attribute of id equals "formation", then the HTML element will be populated with id="formation". By now you should get the jist of attributes, so lets begin by creating our form.

### Opening the form ###

Like all forms, you need to create the opening form tag. You can do so by using the `create()` method. The first argument would be the attributes array. Additionally, `create()` comes with a special attribute called type. You can pass the the value of "file" or "app" which will auto-generate its value, instead of having to apply enctype equals multipart/form-data.

```php
$form = new mjohnson\formation\Form('Login');
echo $form->create();

// Apply the file type and change method
echo $form->create(array('type' => 'file', 'method' => 'get'));

// Outputs: <form id="LoginForm" action="" method="get" enctype="multipart/form-data">
```

### Closing the form ###

This one is rather easy, seeing as how it takes no arguments. Simply place this at the very bottom of your form to close the form and its tags.

```php
echo $form->close();
```

### Creating the input fields ###

Now to create the HTML input fields you would use the methods that match the type of input you want. The following methods are supported: text (default), textarea, password, hidden, image, file, select, radio, checkbox. The first argument should be the name of the field to be applied to the element, and the second argument would be the attributes array. To do the most simply of fields, simply fill in the name argument.

```php
echo $form->text('username');
// Outputs: <input id="LoginUsername" type="text" name="username" value="">

echo $form->password('password', array('maxlength' => 20));
// Outputs: <input id="LoginPassword" type="password" name="password" value="" maxlength="20">

echo $form->textarea('comment', array('rows' => 5, 'cols' => 50));
// Outputs: <textarea cols="50" rows="5" id="LoginComment" name="comment"></textarea>
```

### Dealing with radio's and checkboxes ###

Radio's and checkboxes work a bit different then their text counterparts. For one thing you must pass an attribute value or the validation will fail, and secondly it has a more robust default/checking system. For example, say you want to make sure they read the Terms of Use.

```php
echo $form->checkbox('tos', array('value' => 'yes'));
// Outputs: <input id="LoginTos" type="checkbox" name="tos" value="yes">
```

When the form is posted and validated, you should see the "tos" index within the returned data array. If the checkbox was checked you would see "yes", and if it was not checked it would be empty and or fail the validation (if the rule was applied).

Radios work in the same manner where the value attribute must be supplied.

```php
<?php echo $form->radio('gender', array('value' => 'male', 'default' => true)); ?> Male
<?php echo $form->radio('gender', array('value' => 'female')); ?> Female

/* Outputs:
<input id="LoginMale" type="radio" name="gender" value="male" checked="checked"> Male
<input id="LoginFemale" type="radio" name="gender" value="female"> Female */
```

If you wish to set the "male" radio checked by default, you would pass an attribute of default equals true. However, you may also pass default equals "male", which would accomplish the same result.

### Dealing with select drop downs ###

Now for the last input field type, the select. The select has a special attribute called "options" which is an array that would contain the value option pairs in the drop-down.

```php
echo $form->select('gender', array('options' => array('male' => 'Male', 'female' => 'Female')));

/* Outputs:
<select id="LoginGender" name="gender">
<option value="male">Male</option>
<option value="female">Female</option>
</select> */
```

Selects also support optgroups and can be done by creating a dimensional array. Below is an example of how the array should be formatted and the definite result. You may also set a default and an empty option by using the respective default and empty attributes.

```php
// Games Array
$games = array(
    'First Person Shooter' => array(
        'cs' => 'Counter-Strike', 
        'tf2' => 'Team Fortress 2'
    ),
    'Real-Time Strategy' => array(
        'sc' => 'Starcraft', 
        'wc3' => 'Warcraft 3'
    ),
    'MMORPG' => array(
        'wow' => 'World of Warcraft'
    )
);

echo $form->select('games', array('options' => $games, 'empty' => '-- Select a Game --', 'default' => 'tf2'));

/* Outputs:
<select id="LoginGames" name="games">
<option value="">-- Select a Game --</option>
<optgroup label="First Person Shooter">
    <option value="cs">Counter-Strike</option>
    <option value="tf2" selected="selected">Team Fortress 2</option>
</optgroup>
<optgroup label="Real-Time Strategy">
    <option value="sc">Starcraft</option>
    <option value="wc3">Warcraft 3</option>
</optgroup>
<optgroup label="MMORPG">
    <option value="wow">World of Warcraft</option>
</optgroup>
</select> */
```

### Adding the submit and reset buttons ###

You can't have a form without having buttons to post the data! To create a submit button you would use the `submit()` method, and to create a reset button you would use the `reset()` method, of course! Both take the same arguments, the first would be the text to display on the button, the second would be an array of attributes.

```php
<?php echo $form->submit('Submit'); ?>
<?php echo $form->reset('Reset', array('class' => 'button'))?>

/* Outputs:
<button id="LoginSubmitButton" type="submit">Submit</button>
<button id="LoginResetButton" type="reset" class="button">Reset</button> */
```

### Adding labels ###

What kind of form would you have without labels? A terrible one that's for sure. To create labels you would use the `label()` method. The first argument would be the name of the input field you are applying it to, the second argument would be the text to be used in the label and the third would be the attributes. Simple as that.

```php
<?php echo $form->label('name', 'Name'); ?><br />
<?php echo $form->input('name'); ?>

/* Outputs:
<label for="LoginName">Name</label><br />
<input id="LoginName" type="text" name="name" value=""> */
```

## Posting The Data ##

Now what good is a form if you can't post any data?  The `process()` method can handle all your data posting and processing and supports multiple types of data: `$_POST`, `$_GET`, `$_REQUEST`, etc. The processing functionality should be placed at the top of your page, or if anything above your actual form. The first argument of `process()` would be the data type you want to use, in our examples we will use the most common of types, the `$_POST`.

```php
if ($form->process($_POST)) {
    // Do validation and cleaning
}
```

You can pass a second argument with the name of an input field, usually the submit button's name attribute. When doing so, the form will only process if that name value exists in the data array.

```php
if ($form->process($_POST, 'submit')) {
    // Do validation and cleaning if the submit name exists
}
```

## Validating And Cleaning The Data ##

Validation is always the fun and tedious part about dealing with form data, but don't fret, Formation comes pre-built with many methods of validation. The following types of validation are supported: notEmpty, all characters, alpha, numeric, alphanumeric, boolean, date, decimal, email, extension, ip, phone, date, time, website/url, within a list, within a range, within a character length, matching and custom regex.

The one plus side of Formations validation is that you don't have to call a validation method for each input field, nor do you have to do giant if statements. All you need to do is pass a validation schema, which should be an array containing the fields you want to validate and a set of rules and their options. The schema should be a dimensional array with the first index being the input field name (attribute) and its value being an array of rulesets. The rules will be parsed from top to bottom and will stop once it reaches an error, instead of parsing all rules at once.

As for the rulesets array, the index should be the name of the method (in the Formation class) that you want to use, and the value should be an array of options (if there are any) or the error message to display. You can determine if a rule has options by viewing its respective method in the class. If the method has additional arguments after the `$message` argument, those would be the extra options to apply. In the example below you will notice the checkLength rule has two additional options, these would correlate to the `$max` and `$min` arguments in the method.

By default, all fields are required. If you do not want a field to be required, set required equals false in the ruleset.

```php
$schema = array(
    'name' => array(
        'notEmpty' => 'Your name is required'
    ),
    'email' => array(
        'notEmpty' => 'Your email is required',
        'isEmail' => 'Your email is invalid'
    ),
    'password' => array(
        'notEmpty' => 'Your password is required',
        'isAlnum' => 'Your password may only be alpha-numeric',
        'checkLength' => array('Password must be between 6-12 characters', 12, 6)
    )
);
```

Once you have your schema setup, you would validate it using the `validates()` method. This should be called once you have processed the form, like so.

```php
if ($form->process($_POST)) {
    if ($form->validates($schema)) {
        // Clean data and do your logic
    }

    $errors = $form->getErrors();
}
```

### Cleaning Your Data ###

Once your form validates, the next best thing is to clean your data. This is good practice, especially if the data will be inserted into a database. To clean the data, you would use the `clean()` method, which should be called within the `validates()` if statement.

```php
$clean = $form->clean();
```

By default, it will clean all indexes in the array and strip all HTML. If you want to clean only certain indexes, you would pass an array of their names as the first argument. If you do not want HTML removed, pass false as the second argument.

```php
$clean = $form->clean(array('name'), false);
```

## Displaying Errors ##

To grab the errors in the current form process, you would use the `getErrors()` method. It's good practice to place this within the `process()` if statement so that errors aren't generated unless the form has been posted. The returned data would be an array containing the error messages from the validation schema, paired with the input field that failed.

```php
if ($form->process($_POST)) {
    // Validation here

    $errors = $form->getErrors();
}
```

Once you have your errors returned into the $errors variable, you can just loop the errors in your template to display them. It's that easy!

```php
<?php // Form errors
if ($errors) { ?>
<ul>
    <?php foreach ($errors as $error) { ?>
    <li><?php echo $error; ?></li>
    <?php } ?>
</ul>
<?php } ?>
```

## Convenience Methods ##

Like any of my classes, I try to add in a few convenience methods to make it easier on you in some situations. 

### Manually calling an error ###

If you need to do some error checking and validation that is not supported by the default validation system, you can use the `error()` method. This will add an input and an error message to the error messages list.

```php
$form->error('username', 'Your username is incorrect');
```

### Resetting the data ###

If you need to reset the data and all error messages to null, you can use the `flush()` method. A good place to use it is once a form validates, so that you clear the form so that it cannot be posted again.

```php
$form->flush();
```

### Cleaning data outside of the form ###

If you wish to clean some data externally out of the form, you can use the static method `cleanse()`.

```php
Form::cleanse($data)
```

### Getting a value from the data ###

If you need to grab a single value from the data array, you can use the `get()` method. This is extremely useful for the `checkMatch()` method.

```php
$password = $form->get('password');

// Within validation rule
'checkMatch' => array('The two values do not match!', $form->get('confirmPassword'))
```

### Using all the validation methods outside of the form ###

All the pre-built validation methods are created to be used statically so that you may use them everywhere else in your application.

```php
if (mjohnson\formation\Formation::isEmail($email)) {
    // Correct!
}
```
