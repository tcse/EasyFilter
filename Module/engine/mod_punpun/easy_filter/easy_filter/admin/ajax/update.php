<?php
@error_reporting( E_ALL ^ E_NOTICE );
@ini_set( 'display_errors', true );
@ini_set( 'html_errors', false );
@ini_set( 'error_reporting', E_ALL ^ E_NOTICE );

$module = isset($_POST["module"]) && is_scalar($_POST['module']) ? trim(strip_tags(stripcslashes($_POST['module']))) : false;
$version = isset($_POST["version"]) && is_scalar($_POST['version']) ? trim(strip_tags(stripcslashes($_POST['version']))) : false;

$update = file_get_contents("https://punpun.name/update.php?module={$module}&version={$version}");
echo $update;
?>