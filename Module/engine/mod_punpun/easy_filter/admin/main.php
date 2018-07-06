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
	die("go your way stalker");
}

echo <<<HTML
<div class="card">
	<table class="table card-table">
		<tbody>
			<tr>
				<td>{$this->module_lang['main_ver']}</td>
				<td class="text-right">
					<span class="badge badge-info">v{$this->module_config['version']}</span>
				</td>
			</tr>
			<tr>
				<td><b>{$this->module_lang['main_author']}</b> <a href="https://punpun.name/" target="_blank">PunPun</a></td>
				<td class="text-right">
					<button id="update" type="button" class="btn btn-success">{$this->module_lang['main_update']}</button>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<script>
$(function() {
	var update;
	var module = 'easy_filter';
	var version = '{$this->module_config['version']}';
	$('body').on('click', '#update', function() {
		$.post("/engine/mod_punpun/easy_filter/admin/ajax/update.php", {module: module, version: version}, function(data) {
			data = jQuery.parseJSON(data);
			var icon;
			if (data.icon == 'success') {
				icon = $.sweetModal.ICON_SUCCESS;
			} else if(data.icon == 'warning') {
				icon = $.sweetModal.ICON_WARNING;
			} else {
				icon = $.sweetModal.ICON_ERROR;
			}
			$.sweetModal({
				content: data.data,
				icon: icon
			});
		});
	});
});
</script>
HTML;
?>