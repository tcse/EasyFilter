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

defined('DATALIFEENGINE') || die("IF U DONT GO BACK I WILL RAPE U!");

include ENGINE_DIR . '/mod_punpun/easy_filter/config/filter_block.php';
include ENGINE_DIR . '/mod_punpun/easy_filter/site/filter_design.php';

$xf = xfieldsload();

if ($where_all) {
	$sql_where = ' AND ' . implode(' AND ', $where_all);
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
	
	if (trim($easy_filter_config['options']['not_news']) != '') {
		$where_all[] = "id NOT IN('" . str_replace(',', "','", $easy_filter_config['options']['not_news']) . "')";
	}

	$thisdate = date("Y-m-d H:i:s", time());
	if ($config['no_date'] && !$config['news_future']) {
		$where_all[] = "date < '" . $thisdate . "'";
	}
	
	if ($where_all) {
		$sql_where = ' AND ' . implode(' AND ', $where_all);
	} else {
		$sql_where = '';
	}
}

$sql = $db->query("SELECT xfields FROM " . PREFIX . "_post WHERE xfields!='' AND approve='1' {$sql_where}");

$xf_data = [];
$xf_count = [];

while ($row = $db->get_row($sql)) {
	$xf_news = xfieldsdataload($row['xfields']);
	foreach ($xf_news as $keys => $xf_value) {
		$xf_array = explode(',', $xf_value);
		array_walk($xf_array, function($value, $key) use(&$xf_data, $keys, &$xf_count)
		{
			$v = trim($value);
			$v_key = mb_strtolower($v, "UTF-8");
			if (!$xf_data[$keys][$v_key]) {
				$xf_data[$keys][$v_key] = $v;
			}
			$xf_count[$keys][$v_key]++;
		});
	}
}
$db->free($sql);

$xf_count_sort = [];

if ($where) {
	$sql = $db->query("SELECT xfields FROM " . PREFIX . "_post WHERE xfields!='' AND approve='1' {$where}");
	while ($row = $db->get_row($sql)) {
		$xf_news = xfieldsdataload($row['xfields']);
		foreach ($xf_news as $keys => $xf_value)
		{
			$xf_array = explode(',', $xf_value);
			array_walk($xf_array, function($value, $key) use(&$xf_count_sort, $keys)
			{
				$v = mb_strtolower(trim($value), "UTF-8");
				$xf_count_sort[$keys][$v]++;
			});
		}
	}
	$db->free($sql);
}

$block_filter = '';
foreach ($xf as $index => $value) {
    if ($filter_block[$value[0]]['type'] == 5 && $where) {
        continue;
    }
    
    if ($filter_block[$value[0]]['on'] == 1) {
        $temp_block = $block_design;
        $temp_block = str_replace('{name}', $value[1], $temp_block);
        
        if ($filter_block[$value[0]]['type'] == 4) {
            $temp_design = $design_form[3];
            $temp_design = str_replace('{multiple}', 'multiple', $temp_design);
        } else {
            $temp_design = $design_form[$filter_block[$value[0]]['type']];
            if ($filter_block[$value[0]]['type'] == 3) {
                $temp_design = str_replace('{multiple}', '', $temp_design);
            }
        }
        
        $temp_design = str_replace('{name}', $value[0], $temp_design);
        preg_match("#\\[value\\](.+?)\\[\\/value\\]#i", $temp_design, $value_form);
        $temp_design = str_replace($value_form[0], '', $temp_design);
        
        $value_form = $value_form[1];
    }
    if ($filter_block[$value[0]]['type'] == 5) {
        $value_form = str_replace(["{min}", "{max}"], [min($xf_data[$value[0]]), max($xf_data[$value[0]])], $value_form);
        unset($xf_data[$value[0]]);
        $xf_data[$value[0]][] = str_replace('{key}', $value[0], $value_form);
    } else {
        array_walk($xf_data[$value[0]], function(&$item, $key) use(&$value_form, $value, &$xf_count, $xf_count_sort, $form_field_arr_temp, $where, &$js_form)
        {
            $key_count = mb_strtolower(trim($item), "UTF-8");
            $disabled = '';
            
            if (!$xf_count_sort[$value[0]][$key_count] && $xf_count[$value[0]][$key_count] > 0 && $xf_count_sort) {
                $disabled = 'disabled';
                $xf_count[$value[0]][$key_count] = 0;
            } else {
                if ($xf_count_sort[$value[0]][$key_count] && $xf_count_sort[$value[0]][$key_count] < $xf_count[$value[0]][$key_count]) {
                    $xf_count[$value[0]][$key_count] = $xf_count_sort[$value[0]][$key_count];
                } elseif ($xf_count_sort && !$xf_count_sort[$value[0]][$key_count]) {
                    $xf_count[$value[0]][$key_count] = 0;
                } elseif (!$xf_count_sort && $where) {
                    $xf_count[$value[0]][$key_count] = 0;
                }
                $disabled = $xf_count[$value[0]][$key_count]>0 ? '' : 'disabled';
            }

            $check_value = '';
            if ($xf_count_sort) {
                $check_item = explode(',', $form_field_arr_temp[$value[0]]);
                
                $check_item = array_flip($check_item);
                if (isset($check_item[$item]) && !$disabled) {
                    $check_value = '\\1';
                }
            }
            
            $item_temp = $item;
            $item = preg_replace("#\\[check\\](.*?)\\[/check\\]#is", $check_value, $value_form);
            $item = str_replace(array('{value}', '{key}', '{count}', '{disabled}'), array($item_temp, $value[0], $xf_count[$value[0]][$key_count], $disabled), $item);
            $js_form[$value[0]][$item_temp] = $item;
        });
    }
    
    $temp_design = str_replace("{value}", implode($xf_data[$value[0]]), $temp_design);
    $block_filter .= str_replace("{value}", $temp_design, $temp_block);
}

if (!$where) {
    echo $block_filter;
}
