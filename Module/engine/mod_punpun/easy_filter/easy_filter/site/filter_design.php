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

$block_design = <<<HTML
<div class="fb-sect-name">{name}</div>
<div class="fb-sect fb-sel">
    {value}
</div>
HTML;

$design_form[1] = <<<HTML
[value]<label data-key="{key}-{value}"><input type="checkbox" value="{value}" name="{name}" {disabled} [check]checked[/check]> {value} ({count})</label> [/value]
{value}
HTML;

$design_form[2] = <<<HTML
[value]<label data-key="{key}-{value}"><input type="radio" value="{value}" name="{name}" {disabled} [check]checked[/check]> {value} ({count})</label> [/value]
{value}
HTML;

$design_form[3] = <<<HTML
<select name="{name}" {multiple}>
    <option value=""> - </option>
    [value]<option value="{value}" data-key='{key}-{value}' {disabled} [check]selected[/check]> {value} ({count})</option>[/value]
    {value}
</select>
HTML;

$design_form[5] = <<<HTML
[value]<input type="text" name="slider-{key}" data-min="{min}" data-max="{max}" data-from="{min}" data-to="{max}" data-type="double" data-ionRangeSlider='ionRangeSlider' data-key='{key}'>[/value]
{value}
HTML;
?>