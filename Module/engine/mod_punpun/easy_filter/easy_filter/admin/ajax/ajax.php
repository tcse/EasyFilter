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

@error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -41));
define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once (ENGINE_DIR . '/classes/plugins.class.php');
@header("Content-type: text/html; charset=" . $config['charset']);
date_default_timezone_set($config['date_adjust']);

include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php'));

if ($config["lang_" . $config['skin']] AND file_exists(DLEPlugins::Check(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng'))) {
	include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng'));
} else {
	include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng'));
}

dle_session();

$is_logged = false;
	
require_once (DLEPlugins::Check(ENGINE_DIR . '/modules/sitelogin.php'));

if ($member_id['user_group'] != 1) {
	echo json_encode(['error' => 'empty']);
	exit;
}

include ENGINE_DIR . '/mod_punpun/easy_filter/language/easy_filter.lng';

$action = isset($_POST['action']) ? strip_tags(trim($_POST['action'])) : false;

if ($action == 'cache') {
	clear_cache(['news_easy_filter']);
	echo json_encode(["head" => $easy_filter_lang[24], "text" => $easy_filter_lang[28], "icon" => "success"]);
} elseif ($action == 'options') {
	$data_form = isset($_POST['data_form']) ? $_POST['data_form'] : false;
	if ($data_form) {
		parse_str($data_form, $array_post);

		$handler = fopen(ENGINE_DIR . '/mod_punpun/easy_filter/config/easy_filter.php', "w");
		fwrite($handler, "<?PHP \n\n//Easy Filter by PunPun\n\n\$easy_filter_config = \n");
		fwrite($handler, var_export($array_post, true));
		fwrite($handler, ";\n\n?>");
		fclose($handler);

		echo json_encode(["head" => $easy_filter_lang[24], "text" => $easy_filter_lang[25], "icon" => "success"]);
	}
} elseif ($action == 'filter') {
	$data_form = isset($_POST['data_form']) ? $_POST['data_form'] : false;
	if ($data_form) {
		parse_str($data_form, $array_post);
        
        $block_filter = [];
        foreach ($array_post as $key => $value) {
            if ($value['on'] == 1) {
                if ($key == 'dle_sort') {
                    $temp = [];
                    foreach ($value['option'] as $val) {
                        $temp[] = trim(strip_tags($val));
                    }
                    $block_filter[$key] = ['option' => $temp, 'type' => intval($value['type']), 'on' => 1];
                } else {
                    $block_filter[$key] = ['type' => intval($value['type']), 'on' => 1];
                }
            }
        }
        
        if ($block_filter) {
            $handler = fopen(ENGINE_DIR . '/mod_punpun/easy_filter/config/filter_block.php', "w");
            fwrite($handler, "<?PHP \n\n//Easy Filter by PunPun\n\n\$filter_block = \n");
            fwrite($handler, var_export($block_filter, true));
            fwrite($handler, ";\n\n?>");
            fclose($handler);
        }

		echo json_encode(["head" => $easy_filter_lang[24], "text" => $easy_filter_lang[25], "icon" => "success"]);
	}
}
?>