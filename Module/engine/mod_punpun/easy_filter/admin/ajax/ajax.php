<?php
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

function ClearString ($value, $db)
{
	$value = $db->safesql(trim(stripslashes(strip_tags($value))));
	return $value;
}

include ENGINE_DIR . '/mod_punpun/easy_filter/language/easy_filter.lng';

$action = isset($_POST['action']) ? ClearString($_POST['action'], $db) : false;

if ($action == 'cache') {
	clear_cache(['news_easy_filter']);
	echo json_encode(array("head" => $easy_filter_lang[24], "text" => $easy_filter_lang[28]));
} elseif ($action == 'option') {
	$data_form = isset($_POST['data_form']) ? $_POST['data_form'] : false;
	if ($data_form) {
		foreach ($data_form as $key => $value) {
			if ($value['name'] == "category[]") {
				$category[] = $value['value'];
			} elseif ($value['name'] == "sort[]") {
				$sort[] = $value['value'];
			} else {
				$name_key = str_replace(array("save_con[", "]"), "", $value['name']);
				$save_con[$name_key] = $value['value'];
			}
		}

		if ($category) {
			foreach ($category as $index => $val) {
				$category[$index] = ClearString($val, $db);
			}
			$category = implode(',', $category);
		} else {
			$category = '';
		}

		if ($sort) {
			foreach ($sort as $index => $val) {
				$sort[$index] = ClearString($val, $db);
			}
			$sort = implode(',', $sort);
		} else {
			$sort = '';
		}

		$handler = fopen(ENGINE_DIR . '/mod_punpun/easy_filter/config/easy_filter_config.php', "w");
		fwrite($handler, "<?PHP \n\n//Easy Filter by PunPun\n\n\$easy_filter_config = [\n\n");
		fwrite($handler, "'sort' => '{$sort}',\n\n");
		fwrite($handler, "'category' => '{$category}',\n\n");
		foreach ($save_con as $name => $value) {
			if ($name != 'action') {
				$clear_value =  $db->safesql(trim(strip_tags($value)));
				fwrite($handler, "'{$name}' => \"{$clear_value}\",\n\n");
			}
		}
		fwrite($handler, "];\n\n?>");
		fclose($handler);

		echo json_encode(array("head" => $easy_filter_lang[24], "text" => $easy_filter_lang[25]));
	}
}
?>