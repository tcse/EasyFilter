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

$all_xfield = xfieldsload();
$count_all_xfield = count($all_xfield);

$xfield = [];

$xfield['-'] = '-';
for ($i = 0; $i < $count_all_xfield; $i++) {
	$xfield[$all_xfield[$i][0]] = $all_xfield[$i][1];
	$xfield_sort['xf_' . $all_xfield[$i][0]] = $all_xfield[$i][1];
}


$sort_array = ['p.date' => $this->module_lang[11], 'e.editdate' => $this->module_lang[12], 'e.rating' => $this->module_lang[13], 'p.comm_num' => $this->module_lang[14], 'e.news_read' => $this->module_lang[15]];
if ($xfield_sort) {
	$sort_array = $sort_array + $xfield_sort;
}

$category = CategoryNewsSelection((empty($this->module_config['category']) ? 0 : explode(',', $this->module_config['category'])));

echo <<<HTML
<style>
select {
    width: 300px!important;
}
tr > td:nth-child(2) > select,
tr > td:nth-child(2) > label,
tr > td:nth-child(2) > div {
    float: right!important;
}
</style>
	<form method="post" class="card">
        <div id="option_block_1">
            <table class="table table-striped">
HTML;
$this->showTr(
	$this->module_lang[0],
	$this->module_lang[1],
	false,
	"<select name=\"category[]\" class=\"form-control custom-select\" data-placeholder=\"{$this->module_lang[2]}\" multiple>" . $category . "</select>"
);
$this->showTr(
	$this->module_lang[3],
	$this->module_lang[4],
	'input',
	['options[not_news]', 'text', $this->module_config['options']['not_news']]
);
$this->showTr(
	$this->module_lang[5],
	$this->module_lang[6],
	'input',
	['options[news_limit]', 'number', $this->module_config['options']['news_limit'], false, false, 1, 20]
);

$this->showTr(
	$this->module_lang[22],
	$this->module_lang[23],
	'select_sort',
	['sort_news[]', $sort_array, true, $this->module_config['sort_news'], true, false, $this->module_lang[22]]
);
$this->showTr(
	$this->module_lang[20],
	$this->module_lang[21],
	'select',
	['options[sort_by]', ['asc' => $this->module_lang[18], 'desc' => $this->module_lang[19]], true, $this->module_config['options']['sort_by'], false, false]
);
echo <<<HTML
        </table>
		<button type="submit" name="submit" class="btn btn-lg btn-success">{$this->module_lang['save']}</button>
	</form>
	<script>
		$(function() {            
			$("select[multiple]").chosen({allow_single_deselect:true, no_results_text: '{$this->module_lang[119]}', width: "300px"});
			
			function ajax_save_option() {
				var data_form = $("form").serialize();
				$.post("/engine/mod_punpun/{$this->module_name}/admin/ajax/ajax.php", {data_form: data_form, action: 'options'}, function(data) {
					data = jQuery.parseJSON(data);
					$.toast({
						heading: data.head,
						text: data.text,
						showHideTransition: 'slide',
						position: 'top-right',
						icon: data.icon,
						stack: false
					});
					return false;
				});
				return false;
			}
			
			$("body").on("change", "form", function() {
				ajax_save_option();
			});
			
			$("body").on("submit", "form", function(e) {
				e.preventDefault();
				ajax_save_option();
				return false;
			});
			
		});
	</script>
HTML;
?>