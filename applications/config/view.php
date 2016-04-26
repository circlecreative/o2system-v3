<?php
/*
|-------------------------------------------------------------------------------------------------------
| View Parser
|
| $config[ 'parser' ][ 'driver' ]      The view parser driver to use: standard, smarty, twig, dwoo
| $config[ 'parser' ][ 'php' ]         Enable parser to parse PHP codes.
| $config[ 'parser' ][ 'markdown' ]    Enable parser to parse Markdown codes.
| $config[ 'parser' ][ 'shortcode' ]   Enable parser to parse Wordpress Like shortcodes.
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'parser' ][ 'driver' ] = 'smarty'; // smarty | twig | dwoo
$view[ 'parser' ][ 'cache' ] = APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'parser' . DIRECTORY_SEPARATOR;
$view[ 'parser' ][ 'php' ] = TRUE;
$view[ 'parser' ][ 'markdown' ] = TRUE;
$view[ 'parser' ][ 'bbcode' ] = FALSE;
$view[ 'parser' ][ 'shortcodes' ] = FALSE;
$view[ 'parser' ][ 'security' ] = array(
	'php_handling'        => TRUE,
	'allowed_modifiers'   => array(),
	'allow_constants'     => TRUE,
	'allow_super_globals' => TRUE,
	'allowed_tags'        => TRUE,
);

/*
|-------------------------------------------------------------------------------------------------------
| View Theme
|
| This determines which set of template theme files should be used.
| Leave this BLANK unless you would like to set the template theme.
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'theme' ][ 'default' ] = NULL;

/*
|-------------------------------------------------------------------------------------------------------
| View Assets
|
| This determines which set of template theme files should be used.
| Leave this BLANK unless you would like to set the template theme.
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'assets' ][ 'combine' ] = FALSE;
$view[ 'assets' ][ 'autoload' ] = array(
	'js'       => [ 'jquery', 'system', 'classie', 'wow', 'holder', 'form-plugin' ],
	'css'      => [ 'utilities', 'fonts', 'animate', 'form' ],
	'fonts'    => [ 'font-awesome', 'myriad-pro' ],
	'packages' => array(
		'bootstrap',
		'jquery-ui',
		'pace',
		'toastr',
		'slimscroll',
		'fancybox',
	),
);

/*
|-------------------------------------------------------------------------------------------------------
| View Output
|-------------------------------------------------------------------------------------------------------
|
| $config[ 'output' ][ 'minify' ]       Removes extra characters (usually unnecessary spaces) from your
|                                       output for faster page load speeds.
|                                       Makes your outputted HTML source code less readable.
*/
$view[ 'output' ][ 'minify' ] = FALSE;
$view[ 'output' ][ 'compress' ] = FALSE;

/*
|-------------------------------------------------------------------------------------------------------
| View Cache
|-------------------------------------------------------------------------------------------------------
*/
$view[ 'cache' ][ 'path' ] = APPSPATH . 'cache' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
$view[ 'cache' ][ 'lifetime' ] = 3600;