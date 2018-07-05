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

defined('DATALIFEENGINE') || die("go your way stalker");

include ENGINE_DIR . '/mod_punpun/easy_filter/config/filter_block.php';
include ENGINE_DIR . '/mod_punpun/easy_filter/site/filter_design.php';
include ENGINE_DIR . '/mod_punpun/easy_filter/language/easy_filter.lng';

$xf = xfieldsload();
$count_all_xfield = count($xf);

$xfield = [];

for ($i = 0; $i < $count_all_xfield; $i++) {
	$xfield_sort['xf_' . $xf[$i][0]] = $xf[$i][1];
}

if ($where_all) {
	$sql_where = ' AND ' . implode(' AND ', $where_all);
} else {
	include ENGINE_DIR . '/mod_punpun/easy_filter/config/easy_filter.php';
    
	$where_all = [];
	
	if ($easy_filter_config['category']) {
		if ($config['allow_multi_category']) {
			$where_all[] = "category NOT REGEXP '[[:<:]](" . implode('|', $easy_filter_config['category']) . ")[[:>:]]'";
		} else {
			$where_all[] = "category NOT IN('" . implode("','", $easy_filter_config['category']) . "')";
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
$xf_count_sort_radio = [];

if ($where_sql) {
	$sql = $db->query("SELECT xfields FROM " . PREFIX . "_post WHERE xfields!='' AND approve='1' {$where_sql}");
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
    
    $get_radio = array_filter($filter_block, function($item)
    {
        return $item['type'] == 2 || $item['type'] == 3;
    });
    $radio_c = array_diff_key($where, $get_radio);
    $radio_c = ' AND ' . implode(' AND ', $radio_c);
    $sql = $db->query("SELECT xfields FROM " . PREFIX . "_post WHERE xfields!='' AND approve='1' {$radio_c}");
	while ($row = $db->get_row($sql)) {
		$xf_news = xfieldsdataload($row['xfields']);
		foreach ($xf_news as $keys => $xf_value)
		{
			$xf_array = explode(',', $xf_value);
			array_walk($xf_array, function($value, $key) use(&$xf_count_sort_radio, $keys)
			{
				$v = mb_strtolower(trim($value), "UTF-8");
				$xf_count_sort_radio[$keys][$v]++;
			});
		}
	}
	$db->free($sql);
}

$block_filter = '';
if ($filter_block['dle_sort']) {
    $dle_sort = $filter_block['dle_sort'];
    unset($filter_block['dle_sort']);
}
if ($filter_block['dle_sort_type']) {
    $dle_sort_type = $filter_block['dle_sort_type'];
    unset($filter_block['dle_sort_type']);
}

foreach ($filter_block as $index => $value) {
    if ($value['type'] == 5 && $where) {
        continue;
    }
    
    if ($value['on'] == 1) {
        $temp_block = $block_design;
        $temp_block = str_replace('{name}', $xfield_sort['xf_' . $index], $temp_block);
        
        if ($value['type'] == 4) {
            $temp_design = $design_form[3];
            $temp_design = str_replace('{multiple}', 'multiple', $temp_design);
        } else {
            $temp_design = $design_form[$value['type']];
            if ($value['type'] == 3) {
                $temp_design = str_replace('{multiple}', '', $temp_design);
            }
        }
        
        $temp_design = str_replace('{name}', $index, $temp_design);
        preg_match("#\\[value\\](.+?)\\[\\/value\\]#i", $temp_design, $value_form);
        $temp_design = str_replace($value_form[0], '', $temp_design);
        
        $value_form = $value_form[1];
    }
    
    if ($value['type'] == 5) {
        $value_form = str_replace(["{min}", "{max}"], [min($xf_data[$index]), max($xf_data[$index])], $value_form);
        unset($xf_data[$index]);
        $xf_data[$index][] = str_replace('{key}', $index, $value_form);
    } else {
        array_walk($xf_data[$index], function(&$item, $key) use($value_form, $index, &$xf_count, $xf_count_sort, $xf_count_sort_radio, $form_field_arr_temp, $where, &$js_form, $filter_block)
        {
            $key_count = mb_strtolower(trim($item), "UTF-8");
            $disabled = '';
            
            if (($filter_block[$index]['type'] == 2 || $filter_block[$index]['type'] == 3) && $xf_count_sort_radio[$index][$key_count]) {
                $xf_count_sort[$index][$key_count] = $xf_count_sort_radio[$index][$key_count];
            }
            
            if (!$xf_count_sort[$index][$key_count] && $xf_count[$index][$key_count] > 0 && $xf_count_sort) {
                $disabled = 'disabled';
                $xf_count[$index][$key_count] = 0;
            } else {
                if ($xf_count_sort[$index][$key_count] && $xf_count_sort[$index][$key_count] < $xf_count[$index][$key_count]) {
                    $xf_count[$index][$key_count] = $xf_count_sort[$index][$key_count];
                } elseif ($xf_count_sort && !$xf_count_sort[$index][$key_count]) {
                    $xf_count[$index][$key_count] = 0;
                } elseif (!$xf_count_sort && $where) {
                    $xf_count[$index][$key_count] = 0;
                }
                $disabled = $xf_count[$index][$key_count]>0 ? '' : 'disabled';
            }

            $check_value = '';
            if ($xf_count_sort) {
                $check_item = explode(',', $form_field_arr_temp[$index]);
                
                $check_item = array_flip($check_item);
                if (isset($check_item[$item]) && !$disabled) {
                    $check_value = '\\1';
                }
            }
            
            $item_temp = $item;
            $item = preg_replace("#\\[check\\](.*?)\\[/check\\]#is", $check_value, $value_form);
            $item = str_replace(['{value}', '{key}', '{count}', '{disabled}'], [$item_temp, $index, $xf_count[$index][$key_count], $disabled], $item);
            $js_form[$index][$item_temp] = $item;
        });
    }
    
    $temp_design = str_replace("{value}", implode($xf_data[$index]), $temp_design);
    $block_filter .= str_replace("{value}", $temp_design, $temp_block);
}

$sort_array = ['p.date' => $easy_filter_lang[11], 'e.editdate' => $easy_filter_lang[12], 'e.rating' => $easy_filter_lang[13], 'p.comm_num' => $easy_filter_lang[14], 'e.news_read' => $easy_filter_lang[15]];
if ($xfield_sort) {
    $sort_array = $sort_array + $xfield_sort;
}

function filterSort($sort, $name_sort, $block_design, $design_form, $easy_filter_lang, &$js_form, $option)
{
    if ($sort['on'] == 1) {
        $temp_block = $block_design;
        $temp_block = str_replace('{name}', $easy_filter_lang[40], $temp_block);
        
        if ($sort['type'] == 4) {
            $temp_design = $design_form['dle_sort_3'];
            $temp_design = str_replace('{multiple}', 'multiple', $temp_design);
        } else {
            $temp_design = $design_form['dle_sort_' . $sort['type']];
            if ($sort['type'] == 3) {
                $temp_design = str_replace('{multiple}', '', $temp_design);
            }
        }
        
        $temp_design = str_replace('{name}', $name_sort, $temp_design);
        preg_match("#\\[value\\](.+?)\\[\\/value\\]#i", $temp_design, $value_form);
        $temp_design = str_replace($value_form[0], '', $temp_design);
        $value_form = $value_form[1];
        
        $temp_design_blocks = [];
        
        $sort_array = $name_sort == 'order_by' ? $sort['option'] : $option;
        
        foreach ($sort_array as $key => $value_sort) {
            $temp_design_block = $value_form;
            $check_value = '';
            $check_item = explode(',', $form_field_arr_temp[$name_sort]);
            
            $check_item = array_flip($check_item);
            if (isset($check_item[$value_sort])) {
                $check_value = '\\1';
            }
            
            $temp_design_block = preg_replace("#\\[check\\](.*?)\\[/check\\]#is", $check_value, $temp_design_block);
            $temp_design_block = str_replace('{key}', $name_sort, $temp_design_block);
            
            $temp_design_block = preg_replace('/{value}/', $name_sort == 'order_by' ? $value_sort : $key, $temp_design_block, 2);
            $temp_design_block = preg_replace('/{value}/', $name_sort == 'order_by' ? $option[$value_sort] : $value_sort, $temp_design_block, 1);
            if ($name_sort == 'order_by') {
                $js_form[$name_sort][$name_sort . '-' . $value_sort] = $temp_design_block;
            } else {
                $js_form[$name_sort][$name_sort . '-' . $key] = $temp_design_block;
            }
            $temp_design_blocks[] = $temp_design_block;
        }
        
        $temp_design = str_replace("{value}", implode($temp_design_blocks), $temp_design);
        return str_replace("{value}", $temp_design, $temp_block);
    }
}

if ($dle_sort) {
    $block_filter .= filterSort($dle_sort, 'order_by', $block_design, $design_form, $easy_filter_lang, $js_form, $sort_array);
}

if ($dle_sort_type) {
    $block_filter .= filterSort(
        $dle_sort_type, 'order', $block_design, $design_form, $easy_filter_lang, $js_form, ['asc' => $easy_filter_lang[18], 'desc' => $easy_filter_lang[19]]
    );
}

if (!$where) {
    echo $block_filter;
}
