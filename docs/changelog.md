# Changelog #

*These logs may be outdated or incomplete.*

## 4.0.0 ##

* Updated to PHP 5.3
* Fixed Composer issues
* Separated Form and Formation into separate files

## 3.2 ##

* Added Composer support
* Replaced errors with exceptions
* Refactored to use strict equality

## 3.1 ##

* Added image validation for dimensions and filesize
* Fixed problems where 0 wasn't being considered as a true value
* Fixed multiple checkboxes and select options
* Refactoring how $_FILES work

## 3.0 ##

* Added escaping to cleanse()
* Added a config system to change doctypes and models
* Added a button() method to create button tags
* Added an image() method and file validation
* Added support for $_FILES
* Fixes for radios, selects and checkboxes
* Separated validation methods into Formation and builder methods into Form
* Renamed input() to be type specific

## 2.2 ##

* Updated the PHP Doc blocks and variable, method names
* Fixed a bug where XSS type attacks could be passed before validation

## 2.1 ##

* Added support for multi-byte and UTF-8 characters

## 2.0 ##

* First initial release of Formation
