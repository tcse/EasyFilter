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

include ENGINE_DIR . "/mod_punpun/easy_filter/config/filter_block.php";

$all_xfield = xfieldsload();
$count_all_xfield = count($all_xfield);

$xfield = [];

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
</style>
	<form method="post" class="card">
        <table class="table table-striped">
            <tr>
                <th>{$this->module_lang[36]}</th>
                <th class="text-center">{$this->module_lang[37]}</th>
                <th class="text-center">{$this->module_lang[38]}</th>
            </tr>
HTML;
for ($i = 0; $i < $count_all_xfield; $i++) {
echo <<<HTML
<tr>
    <td>
        <h6>{$all_xfield[$i][1]}</h6>
        <span class="note large">{$all_xfield[$i][0]}</span>
    </td>
    <td class="text-center">
HTML;
    echo $this->selectForm([
        "{$all_xfield[$i][0]}[type]", 
        [
            1 => $this->module_lang[30],
            2 => $this->module_lang[31],
            3 => $this->module_lang[32],
            4 => $this->module_lang[33],
            5 => $this->module_lang[34]
        ], 
        true, 
        $filter_block[$all_xfield[$i][0]]['type']]
    );
    $checked = '';
    if ($filter_block[$all_xfield[$i][0]]['on'] == 1) {
        $checked = 'checked';
    }
echo <<<HTML
    </td>
    <td class="text-center">
        <label class="custom-switch">
            <input class="custom-switch-input" type="checkbox" name="{$all_xfield[$i][0]}[on]" value="1" {$checked}>
            <span class="custom-switch-indicator"></span>
        </label>
    </td>
</tr>
HTML;
}
echo <<<HTML
        </table>
		<button type="submit" name="submit" class="btn btn-lg btn-success">{$this->module_lang['save']}</button>
	</form>
	<script>
		$(function() {
            
			$("select[multiple]").chosen({allow_single_deselect:true, no_results_text: '{$this->module_lang[119]}', width: "300px"});
			
			function ajax_save_option() {
				var data_form = $("form").serialize();
				$.post("/engine/mod_punpun/{$this->module_name}/admin/ajax/ajax.php", {data_form: data_form, action: 'filter'}, function(data) {
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

			$("body").on("submit", "form", function(e) {
				e.preventDefault();
				ajax_save_option();
				return false;
			});
			
		});
	</script>
HTML;
?>