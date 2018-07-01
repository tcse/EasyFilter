<?PHP
/*
=====================================================
Easy Filter 2.0.0
-----------------------------------------------------
Author: PunPun
-----------------------------------------------------
Site: http://punpun.name/
-----------------------------------------------------
Copyright (c) 2018 PunPun
=====================================================
Данный код защищен авторскими правами
*/

if (!defined('DATALIFEENGINE') OR !defined('LOGGED_IN')) {
	header("HTTP/1.1 403 Forbidden");
	header('Location: ../../');
	die("Hacking attempt!");
}

if ($member_id['user_group'] != 1) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

include ENGINE_DIR . '/mod_punpun/easy_filter/admin/index.php';
?>