<?php
defined('DATALIFEENGINE') || die("IF U DONT GO BACK I WILL RAPE U!");

$xf = xfieldsload();

$tpl_block = file_get_contents(TEMPLATE_DIR . '/mod_punpun/easy_filter/filter_block.tpl');
$tpl_value = file_get_contents(TEMPLATE_DIR . '/mod_punpun/easy_filter/filter_value.tpl');

if ($where_all) {
	$where_all = ' AND ' . implode(' AND ', $where_all);
} else {
	include ENGINE_DIR . '/mod_punpun/easy_filter/config/easy_filter_config.php';

	$where_all = [];
	
	if (trim($easy_filter_config['category']) != '') {
		if ($config['allow_multi_category']) {
			$where_all[] = "category NOT REGEXP '[[:<:]](" . str_replace(',', '|', $easy_filter_config['category']) . ")[[:>:]]'";
		} else {
			$where_all[] = "category NOT IN('" . str_replace(',', "','", $easy_filter_config['category']) . "')";
		}
	}
	
	if (trim($easy_filter_config['news']) != '') {
		$where_all[] = "id NOT IN('" . str_replace(',', "','", $easy_filter_config['news']) . "')";
	}

	$thisdate = date("Y-m-d H:i:s", time());
	if ($config['no_date'] && !$config['news_future']) {
		$where_all[] = "date < '" . $thisdate . "'";
	}
	
	$where_all = ' AND ' . implode(' AND ', $where_all);
}

$sql = $db->query("SELECT xfields FROM " . PREFIX . "_post WHERE xfields!='' AND approve='1' {$where_all}");
$xf_data = [];
$xf_count = [];

while ($row = $db->get_row($sql)) {
	$xf_news = xfieldsdataload($row['xfields']);
	foreach ($xf_news as $keys => $xf_value) {
		$xf_array = explode(",", $xf_value);
		array_walk($xf_array, function($value, $key) use(&$xf_data, $keys, &$xf_count)
		{
			$v = trim($value);
			if (!$xf_data[$keys][$v]) {
				$xf_data[$keys][$v] = $v;
			}
			$xf_count[$v]++;
		});
	}
}

$db->free($sql);

$xf_count_sort = [];

if ($where) {
	$where = " AND " . $where;
	$sql = $db->query("SELECT xfields FROM " . PREFIX . "_post WHERE xfields!='' AND approve='1' {$where}");
	while ($row = $db->get_row($sql)) {
		$xf_news = xfieldsdataload($row['xfields']);
		foreach ($xf_news as $keys => $xf_value)
		{
			$xf_array = explode(",", $xf_value);
			array_walk($xf_array, function($value) use(&$xf_count_sort)
			{
				$v = trim($value);
				$xf_count_sort[$v]++;
			});
		}
	}
	$db->free($sql);
}

$js_form = [];
foreach ($xf as $index => $value) {
	if (strpos($tpl_block, "[{$value[0]}]") !== false) {
		preg_match("#\\[{$value[0]}\\](.*?)\\[/{$value[0]}\\]#is", $tpl_value, $value_design);
		if (strpos($value_design[1], "slider-") !== false && $where) {
			unset($xf_data[$value[0]]);
		} else {
			$tpl_block = preg_replace("#\\[{$value[0]}\\](.*?)\\[/{$value[0]}\\]#is", '\\1', $tpl_block);

			if (strpos($value_design[1], "slider-") !== false) {
				$value_design[1] = str_replace(["{min-{$value[0]}}", "{max-{$value[0]}}"], [min($xf_data[$value[0]]), max($xf_data[$value[0]])], $value_design[1]);
				unset($xf_data[$value[0]]);
				$xf_data[$value[0]][] = str_replace('{key}', $value[0], $value_design[1]);
			} else {
				array_walk($xf_data[$value[0]], function(&$item, $key) use($value_design, $value, &$xf_count, $xf_count_sort, $form_field_arr_temp, $where, &$js_form)
				{
					if (!$xf_count_sort[$item] && $xf_count[$item] > 0 && $xf_count_sort) {
						$disabled = 'disabled';
						$xf_count[$item] = 0;
					} else {
						if ($xf_count_sort[$item]) {
							$xf_count[$item] = $xf_count_sort[$item] == $xf_count[$item] ? $xf_count[$item] : '+' . $xf_count_sort[$item];
						} elseif ($xf_count_sort && !$xf_count_sort[$item]) {
							$xf_count[$item] = 0;
						} elseif (!$xf_count_sort && $where) {
							$xf_count[$item] = 0;
						}
						$disabled = $xf_count[$item]>0 ? '' : 'disabled';
					}

					$check_value = '';
					if ($xf_count_sort) {
						$check_item = explode(',', $form_field_arr_temp[$value[0]]);
						
						$check_item = array_flip($check_item);
						if (isset($check_item[$item]) && !$disabled) {
							$check_value = '\\1';
						} else {
							$check_value = '';
						}
					}
					$item_temp = $item;
					$value_design[1] = preg_replace("#\\[check\\](.*?)\\[/check\\]#is", $check_value, $value_design[1]);
					$item = str_replace(array('{value}', '{key}', '{count}', '{disabled}'), array($item, $value[0], $xf_count[$item], $disabled), $value_design[1]);
					$js_form[$value[0]][$item_temp] = $item;
				});
			}
			$tpl_block = str_replace("{{$value[0]}-value}", implode($xf_data[$value[0]]), $tpl_block);
		}
	}
}

$tpl_block = preg_replace("#\\[(.+?)\\](.*?)\\[/\\1\\]#is", "", $tpl_block);

if (!$where) {
	echo $tpl_block;
}
?>
