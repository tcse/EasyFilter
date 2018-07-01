<?php
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
	die("Hacking attempt!");
}

$module_name = 'easy_filter';

if (file_exists(ENGINE_DIR . "/mod_punpun/{$module_name}/language/{$module_name}.lng")) {
	require_once (ENGINE_DIR . "/mod_punpun/{$module_name}/language/{$module_name}.lng");
} else {
	die("Language file not found");
}

include ENGINE_DIR . "/mod_punpun/{$module_name}/config/{$module_name}.php";

include __DIR__ . '/class.admin.php';

$easy_filter_config['version'] = '2.0.0';

$admin = new AdminTemplate($db, $member_id, $user_group, $config, $easy_filter_config, $easy_filter_lang, $cat_info, $module_name);

$admin->setMenu(
	[
		['', $module_name, 'home', $easy_filter_lang['main']],
		['options', "{$module_name}&action=options", 'settings', $easy_filter_lang['settings']],
        ['filter', "{$module_name}&action=filter", 'filter', $easy_filter_lang['filter']],
	], $action);

$admin->headerTemplate();
$admin->content($action);
$admin->footerTemplate();
?>