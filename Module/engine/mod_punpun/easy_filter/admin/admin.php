<?PHP
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

if( !defined('DATALIFEENGINE') OR !defined('LOGGED_IN')) {
	header("HTTP/1.1 403 Forbidden");
	header('Location: ../../');
	die("Hacking attempt!");
}

if ($member_id['user_group'] != 1) {
	msg("error", $lang['addnews_denied'], $lang['db_denied']);
}

include ENGINE_DIR . '/mod_punpun/easy_filter/language/easy_filter.lng';
include ENGINE_DIR . '/mod_punpun/easy_filter/config/easy_filter_config.php';

function showRow($title = "", $description = "", $field = "", $class = "")
{
	echo "<tr>
	<td class=\"col-xs-6 col-sm-6 col-md-7\"><h6 class=\"media-heading text-semibold\">{$title}</h6><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td>
	<td class=\"col-xs-6 col-sm-6 col-md-5\">{$field}</td>
	</tr>";
}

function makeDropDown($options, $name, $selected)
{
	$output = "<select class=\"uniform\" name=\"$name\">\r\n";
	foreach ($options as $value => $description) {
		$output .= "<option value=\"$value\"";
		if ($selected == $value) {
			$output .= " selected ";
		}
		$output .= ">$description</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function makeCheckBox($name, $selected)
{
	$selected = $selected ? "checked" : "";
	return "<input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}>";
}

function makeDropDowns($options, $selected, $check = false)
{
	foreach($options as $index => $value) {
		if ($check) {
			$output .= "<option value=\"$value\"";
		} else {
			$output .= "<option value=\"$index\"";
		}
		if (is_array($selected)) {
			foreach ($selected as $element) {
				if($check && $element == $value) {
					$output .= " selected ";
				} elseif (!$check && $element == $index) {
					$output .= " selected ";
				}
			}
		} elseif ($selected == $index && !$check) {
			$output .= " selected ";
		} elseif ($selected == $value && $check) {
			$output .= " selected ";
		}
		$output .= ">$value</option>\n";
	}
	return $output;
}

function showSelect($name, $value, $check = false)
{
	if(!$check) $multiple = "multiple";
	return "<select name=\"{$name}\" {$multiple} style=\"width:100%;\">{$value}</select>";
}

function select_sortForm($data, $easy_filter_lang)
{
	$opt = false;
	foreach ($data[1] as $key => $val) {
		if (strpos($key, 'xf_') !== false && !$opt) {
			$opt = true;
			$output = "<optgroup label=\"{$easy_filter_lang[16]}\">" . $output . "</optgroup><optgroup label=\"{$easy_filter_lang[17]}\">";
		}
		if ($data[2]) {
			$output .= "<option value=\"{$key}\"";
		} else {
			$output .= "<option value=\"{$val}\"";
		}
		if (is_array($data[3])) {
			foreach ($data[3] as $element) {
				if ($data[2] && $element == $key) {
					$output .= " selected ";
				} elseif (!$data[2] && $element == $val) {
					$output .= " selected ";
				}
			}
		} elseif ($data[2] && $data[3] == $key) {
			$output .= " selected ";
		} elseif (!$data[2] && $data[3] == $val) {
			$output .= " selected ";
		}
		$output .= ">{$val}</option>\n";
	}
	$output .= "</optgroup>";
	$input_elemet = $data[5] ? ' disabled' : '';
	$input_elemet .= $data[4] ? ' multiple' : '';
	$input_elemet .= $data[6] ? " data-placeholder=\"{$data[6]}\"" : '';
return <<<HTML
	<select name="{$data[0]}" class="form-control custom-select" {$input_elemet}>
		{$output}
	</select>
HTML;
}

$all_xfield = xfieldsload();
$xf_select = array();
for ($i = 0; $i < count($all_xfield); $i++) {
	$xf_select[$all_xfield[$i][0]] = $all_xfield[$i][1];
	$xfield_sort['xf_' . $all_xfield[$i][0]] = $all_xfield[$i][1];
}

$sort_array = ['p.date' => $easy_filter_lang[11], 'e.editdate' => $easy_filter_lang[12], 'e.rating' => $easy_filter_lang[13], 'p.comm_num' => $easy_filter_lang[14], 'e.news_read' => $easy_filter_lang[15]];
if ($xfield_sort) {
	$sort_array = $sort_array + $xfield_sort;
}

$category = CategoryNewsSelection((empty($easy_filter_config['category']) ? 0 : explode(',', $easy_filter_config['category'])));

echoheader("<i class=\"fa fa-filter position-left\"></i><span class=\"text-semibold\">{$easy_filter_lang['title']}</span>", $easy_filter_lang['descr']);
echo <<<HTML
<form action="" method="post" class="systemsettings">
	<div id="general" class="panel panel-flat">
		<div class="panel-body">{$easy_filter_lang['descr']}</div>
		<div class="table-responsive">
			<table class="table table-striped">
HTML;

showRow($easy_filter_lang[0], $easy_filter_lang[1], "<select data-placeholder=\"{$easy_filter_lang[2]}\" name=\"category[]\" multiple style=\"width:100%;max-width:350px;\">{$category}</select>");
showRow($easy_filter_lang[3], $easy_filter_lang[4], "<input type=\"text\" class=\"form-control\" name=\"save_con[news]\" value=\"{$easy_filter_config['news']}\">");
showRow($easy_filter_lang[5], $easy_filter_lang[6], "<input type=\"number\" class=\"form-control\" min=\"1\" name=\"save_con[count_first]\" value=\"{$easy_filter_config['count_first']}\">");
showRow($easy_filter_lang[9], $easy_filter_lang[10], makeCheckBox('save_con[allow_cache]', $easy_filter_config['allow_cache']));
showRow($easy_filter_lang[22], $easy_filter_lang[23], select_sortForm(['sort[]', $sort_array, true, empty($easy_filter_config['sort']) ? [] : explode(',', $easy_filter_config['sort']), true, false, $easy_filter_lang[23]], $easy_filter_lang));
showRow($easy_filter_lang[20], $easy_filter_lang[21], makeDropDown(['asc' => $easy_filter_lang[18], 'desc' => $easy_filter_lang[19]], 'save_con[sort_by]', $easy_filter_config['sort_by']));
echo <<<HTML
			</table>
		</div>	
		<div class="panel-footer">
			<button type="submit" name="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['news_save']}</button>
			<button type="button" id="clear_cache" class="btn bg-danger btn-sm btn-raised position-left"><i class="fa fa-trash position-left"></i>{$lang['btn_clearcache']}</button>
		</div>
	</div>
	<input type="hidden" name="action" value="save" />
</form>
<script>
$(function(){
	function ajax_save_option() {
		$("div.jGrowl").jGrowl("close");
		var data_form = $("form").serializeArray();
		$.post("/engine/mod_punpun/easy_filter/admin/ajax/ajax.php", {data_form: data_form, action: 'option'}, function(data) {
			data = jQuery.parseJSON(data);
			Growl.info({title: data.head, text: data.text});
			return false;
		});
		return false;
	}
	
	$("body").on("click", "#clear_cache", function(e) {
		e.preventDefault();
		$("div.jGrowl").jGrowl("close");
		if ($('[name="save_con[allow_cache]"]').is(":checked")) {
			$.post("/engine/mod_punpun/easy_filter/admin/ajax/ajax.php", {action: 'cache'}, function(data) {
				data = jQuery.parseJSON(data);
				Growl.info({title: data.head, text: data.text});
				return false;
			});
		} else {
			Growl.error({title: "{$easy_filter_lang[26]}", text: "{$easy_filter_lang[27]}"});
		}
	});

	$("body").on("change", "form", function() {
		ajax_save_option();
	});

	$("body").on("submit", "form", function(e) {
		e.preventDefault();
		ajax_save_option();
	});
	
	$('select:not(.uniform)').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
});
</script>
HTML;
echofooter();
?>