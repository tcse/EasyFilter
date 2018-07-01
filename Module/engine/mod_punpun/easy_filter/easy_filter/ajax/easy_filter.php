<?php
/*
=====================================================
Easy Filter 1.1
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
define('ROOT_DIR', substr(dirname(__FILE__), 0, -35));

define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once (ENGINE_DIR . '/classes/plugins.class.php');
@header("Content-type: text/html; charset=" . $config['charset']);
date_default_timezone_set($config['date_adjust']);

include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php'));

if ($_REQUEST['skin']) {
	$_REQUEST['skin'] = $_REQUEST['dle_skin'] = trim(totranslit($_REQUEST['skin'], false, false));
}

if ($_REQUEST['dle_skin']) {
	$_REQUEST['dle_skin'] = trim(totranslit($_REQUEST['dle_skin'], false, false));
	
	if($_REQUEST['dle_skin'] AND @is_dir(ROOT_DIR . '/templates/' . $_REQUEST['dle_skin'])) {
		$config['skin'] = $_REQUEST['dle_skin'];
	} else {
		$_REQUEST['dle_skin'] = $_REQUEST['dle_skin'] = $config['skin'];
	}
} elseif ($_COOKIE['dle_skin']) {
	$_COOKIE['dle_skin'] = trim(totranslit( (string)$_COOKIE['dle_skin'], false, false ));

	if ($_COOKIE['dle_skin'] AND is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'])) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if ($config["lang_" . $config['skin']] AND file_exists(DLEPlugins::Check(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng'))) {
	include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng'));
} else {
	include_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $config['langs'] . '/website.lng'));
}

require_once (DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php'));

if (!$config['http_home_url']) {
	$config['http_home_url'] = explode("engine/mod_punpun/easy_filter/ajax/easy_filter.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset($config['http_home_url']);
}

if (strpos($config['http_home_url'], "//") === 0) {
	$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https:".$config['http_home_url'] : $config['http_home_url'] = "http:".$config['http_home_url'];
} elseif (strpos($config['http_home_url'], "/") === 0) {
	$config['http_home_url'] = isSSL() ? $config['http_home_url'] = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'] : "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
} elseif(isSSL() AND stripos($config['http_home_url'], 'http://') !== false) {
	$config['http_home_url'] = str_replace( "http://", "https://", $config['http_home_url'] );
}

if (substr($config['http_home_url'], -1, 1) != '/') {
	$config['http_home_url'] .= '/';
}

dle_session();

$user_group = get_vars("usergroup");

if (!$user_group) {
	$user_group = [];

	$db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");
	while ($row = $db->get_row()) {
		$user_group[$row['id']] = [];
		foreach ($row as $key => $value) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	}
	set_vars("usergroup", $user_group);
	$db->free();
}

$cat_info = get_vars("category");

if (!is_array($cat_info)) {
	$cat_info = [];

	$db->query("SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC");
	while ($row = $db->get_row()) {
		$cat_info[$row['id']] = [];
		foreach ($row as $key => $value) {
			$cat_info[$row['id']][$key] = stripslashes($value);
		}
	}
	set_vars("category", $cat_info);
	$db->free();
}

$is_logged = false;
	
require_once (DLEPlugins::Check(ENGINE_DIR . '/modules/sitelogin.php'));

if (!$is_logged) {
	$member_id['user_group'] = 5;
}

if ($is_logged && $member_id['banned'] == "yes") {
	echo json_encode(['error' => 'empty']);
	exit;
}

define('TEMPLATE_DIR', ROOT_DIR . '/templates/' . $config['skin']);
$_TIME = time();

$form_field = str_ireplace(["<?", "?>", "eval", "$", "@"], '', $_POST['form_field']);
$form_field = strip_tags($form_field);
$form_field = urldecode(trim($form_field));

if(!$form_field OR trim($form_field) == '') {
	echo json_encode(['error' => 'empty']);
	exit;
}

$form_field = explode('&', $form_field);

$form_field_arr = [];
foreach ($form_field as $val) {
	if (!substr_count($val, '=&')) {
		$val_arr = explode('=', $val);
		if ($val_arr[1] != '') {
			if (array_key_exists($val_arr[0], $form_field_arr)) {
				$form_field_arr[$val_arr[0]] .= ',' . $val_arr[1];
			} else {
				$form_field_arr[$val_arr[0]] = $val_arr[1];
			}
		}
	} else {
		$val_arr = explode('=&', $val);
		$val_arr = explode('=', $val_arr[1]);
		if ($val_arr[1] != '') {
			if (array_key_exists($val_arr[0], $form_field_arr)) {
				$form_field_arr[$val_arr[0]] .= ',' . $val_arr[1];
			} else {
				$form_field_arr[$val_arr[0]] = $val_arr[1];
			}
		}
	}
}

$sort_by_const = [
	"date",
	"title",
	"comm_num",
	"news_read",
	"autor",
	"category",
	"rating"
];

function sortSqlFilter ($key, $value, $db, $form_field_arr, $sort_by_const)
{
	if ($key == 'order_by') {
		$value = $db->safesql($value);
		if (substr_count($value, 'dec_')) {
			$order_by = substr_replace($value, '', 0, 4);
			$order_by = "ABS(SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$order_by}|', -1), '||', 1))";
		} elseif (substr_count($value, 'date_')) {
			$order_by = substr_replace($value, '', 0, 5);
			$order_by = intval($order_by);
			$order_by = "AND date >= NOW() - INTERVAL {$order_by} DAY";
		} elseif (in_array($value, $sort_by_const)) {
			$order_by = $value;
		} else {
			$order_by = "SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$value}|', -1), '||', 1)";
		}
		
		if ($form_field_arr['order'] && ($form_field_arr['order'] == 'asc' || $form_field_arr['order'] == 'desc')) {
			$order_by .= ' ' . $form_field_arr['order'];
		} else {
			$order_by .= ' DESC';
		}
	} elseif ($key == 'order_by_one') {
		$order_by_one = explode(";", $value);
		
		$order_by_one[0] = $db->safesql(trim($order_by_one[0]));
		$order_by_one[1] = $db->safesql(trim($order_by_one[1]));
		
		if (substr_count($order_by_one[0], 'dec_')) {
			$order_by = substr_replace($order_by_one[0], '', 0, 4);
			$order_by = "ABS(SUBSTRING_INDEX( SUBSTRING_INDEX( xfields,  '{$order_by}|', -1 ) ,  '||', 1 ))";
		} elseif (in_array($order_by_one[0], $sort_by_const)) {
			$order_by = $order_by_one[0];
		} else {
			$order_by = "SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$order_by_one[0]}|', -1), '||', 1)";
		}
		
		if ($order_by_one[1] && ($order_by_one[1] == 'asc' || $order_by_one[1] == 'desc')) {
			$order_by .= ' ' . $order_by_one[1];
		} else {
			$order_by .= ' DESC';
		}
	}
	return $order_by;
}

include ENGINE_DIR . '/mod_punpun/easy_filter/config/easy_filter_config.php';

$form_field_arr_temp = $form_field_arr;
$order_by = [];
$where = [];

foreach ($form_field_arr as $key => &$value) {
	if (substr_count($value, ',')) {
		$value = explode(',', $value);
	}
	$key = $db->safesql($key);
	if (is_array($value)) {
		if ($key == 'order_by' || $key == 'order_by_one') {
			$order_by[] = sortSqlFilter($key, $value, $db, $form_field_arr, $sort_by_const);
		} else {
			if ($key != 'order') {
				foreach ($value as $index => &$val) {
					$val = $db->safesql($val);
					$xf_filter_arr[$key][] = "SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$key}|', -1), '||', 1) LIKE '%{$val}%'";
				}
				$where[] =  '(' . implode(' OR ', $xf_filter_arr[$key]) . ')';
			}
		}
	} elseif ($value != NULL && $value != "" && is_scalar($value)) {
		if(preg_match( "#^slider-(.+)#is", $key, $matches)) {
			if(trim($matches[1]) != "") {
				$key = $matches[1];
				$value = explode(';', $value);
				$value[1] = $db->safesql($value[1]);
				$value[0] = $db->safesql($value[0]);
				$where[] = "ABS(SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$key}|', -1 ), '||', 1))>={$value[0]} AND ABS(SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$key}|', -1), '||', 1))<={$value[1]}";
			}
		} elseif ($key == 'order_by' || $key == 'order_by_one') {
			$order_by[] = sortSqlFilter($key, $value, $db, $form_field_arr, $sort_by_const);
		} else {
			if ($key != 'order') {
				$value = $db->safesql($value);
				$where[] = "SUBSTRING_INDEX(SUBSTRING_INDEX(xfields,  '{$key}|', -1), '||', 1) LIKE '%{$value}%'";
			}
		}
	}
}

$where_all = [];

if (trim($easy_filter_config['category']) != '') {
	if ($config['allow_multi_category']) {
		$where[] = "category NOT REGEXP '[[:<:]](" . implode('|', $easy_filter_config['category']) . ")[[:>:]]'";
		$where_all[] = "category NOT REGEXP '[[:<:]](" . implode('|', $easy_filter_config['category']) . ")[[:>:]]'";
	} else {
		$where[] = "category NOT IN('" . implode("','", $easy_filter_config['category']) . "')";
		$where_all[] = "category NOT IN('" . implode("','", $easy_filter_config['category']) . "')";
	}
}

if (trim($easy_filter_config['options']['not_news']) != '') {
	$where[] = "id NOT IN('" . str_replace(',', "','", $easy_filter_config['options']['not_news']) . "')";
	$where_all[] = "id NOT IN('" . str_replace(',', "','", $easy_filter_config['options']['not_news']) . "')";
}

$thisdate = date("Y-m-d H:i:s", time());
if ($config['no_date'] && !$config['news_future']) {
	$where[] = "date < '" . $thisdate . "'";
	$where_all[] = "date < '" . $thisdate . "'";
}

if ($where) {
	$where = ' AND ' . implode(' AND ', $where);
} else {
	unset($where);
}

$all_news = isset($_POST['all_news']) && intval($_POST['all_news']) > 0 ? intval($_POST['all_news']) : false;
$now_news = isset($_POST['now_news']) && intval($_POST['now_news']) > 0 ? intval($_POST['now_news']) : 0;

if (!$all_news) {
	$count_news = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE approve='1'" . $where);
	$all_news = $count_news['count'];
}

if ($order_by) {
	$order_by_sql = ' ORDER BY ' . implode(', ', $order_by);
} elseif ($easy_filter_config['sort'] != '') {
	$count_sort = count($easy_filter_config['sort']);
	$sort_array = [];
	for ($i = 0; $i < $count_sort; $i++) {
		if (strpos($easy_filter_config['sort'][$i], 'xf_') !== false) {
			$easy_filter_config['sort'][$i] = substr_replace($easy_filter_config['sort'][$i], '', 0, 3);
			$sort_array[] = "ABS(SUBSTRING_INDEX(SUBSTRING_INDEX(xfields, '{$easy_filter_config['sort'][$i]}|', -1), '||', 1)) " . (($easy_filter_config['sort_by']!='') ? $easy_filter_config['sort_by'] : 'DESC');;
		} else {
			$sort_array[] = $easy_filter_config['sort'][$i] . (($easy_filter_config['sort_by']!='') ? ' ' . $easy_filter_config['sort_by'] : ' DESC');
		}
	}
	$order_by_sql = " ORDER BY " . implode(', ', $sort_array);
} else {
	$order_by_sql = ' ORDER BY p.date DESC';
}

$easy_filter_config['options']['news_limit'] = intval($easy_filter_config['options']['news_limit']) > 0 ? intval($easy_filter_config['options']['news_limit']) : 10;
$sql_result = $db->query("SELECT p.id, p.autor, p.date, p.short_story, p.full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE approve='1' {$where} {$order_by_sql} LIMIT {$now_news},{$easy_filter_config['options']['news_limit']}");

$now_news = $now_news != 0 ? $now_news + $easy_filter_config['options']['news_limit'] : 0;

$allow_active_news = true;
$tpl = new dle_template();
$tpl->dir = TEMPLATE_DIR;
$tpl->load_template('shortstory.tpl');

include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/show.custom.php'));

if (!$tpl->result['content']) {
	$tpl->load_template('info.tpl');
	$tpl->set('{error}', "По вашему запросу, материала не найдено.");
	$tpl->set('{title}', "Ошибка!");
	$tpl->compile('content');
	$tpl->clear();
}

include ENGINE_DIR . '/mod_punpun/easy_filter/site/filter.php';

if (trim($tpl->result["content"]) != "") {
	$tpl->result["content"] = PHP_EOL . "<!-- Easy Filter by PunPun.name -->" . PHP_EOL . $tpl->result["content"] . PHP_EOL . "<!-- Easy Filter by PunPun.name -->" . PHP_EOL;
}

if ($all_news > $easy_filter_config['options']['news_limit'] && $now_news == 0) {
$tpl->result['content'] .=<<<HTML
<div class="bottom-nav ignore-select" id="bottom-nav">
	<div class="nav-load" id="nav-load"><div>Загрузить еще</div></div>
</div>
HTML;
}

$tpl->result['content'] = str_replace('{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['content']);
$data_output['news'] = $tpl->result['content'];
$data_output['now_news'] = $now_news;
$data_output['all_news'] = $all_news;
$data_output['limit'] = $easy_filter_config['options']['news_limit'];
$data_output['js_form'] = $js_form;

$data_output = json_encode($data_output);
echo $data_output;

?>