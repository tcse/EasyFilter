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

if(!defined('DATALIFEENGINE') OR !defined('LOGGED_IN')) {
	die("go your way stalker");
}

class AdminTemplate
{
    private
	$db,
	$member,
	$user_group,
	$dle_config,
	$module_config,
	$module_lang,
    $cat_info,
    $module_name;
	
	public $menu;
	
	public function __construct($db, $member_id, $user_group, $config, $module_config, $module_lang, $cat_info, $module_name)
    {
        $this->db = $db;
        $this->member = $member_id;
        $this->user_group = $user_group;
		$this->dle_config = $config;
		$this->module_config = $module_config;
		$this->module_lang = $module_lang;
		$this->cat_info = $cat_info;
        $this->module_name = $module_name;
    }
	
	function setMenu($menu, $action)
	{
		foreach ($menu as $menu_item) {
			$active = $action == $menu_item[0] ? " active" : "";
$menu_items[] = <<<HTML
		<li class="nav-item"><a href="{$PHP_SELF}?mod={$menu_item[1]}" class="nav-link{$active}"><i class="fe fe-{$menu_item[2]}"></i> {$menu_item[3]}</a></li>
HTML;
		}
		$this->menu = implode($menu_items);
	}
	
	public function headerTemplate()
	{
		if ($this->dle_config['version_id'] >= "10.5") {
			if (count(explode("@", $this->member['foto'])) == 2) {
				$avatar = '//www.gravatar.com/avatar/' . md5(trim($this->member['foto'])) . '?s=' . intval($this->user_group[$this->member['user_group']]['max_foto']);
			} else {
				if ($this->member['foto']) {
					$avatar = (strpos($this->member['foto'], "//") === 0) ? "http:".$this->member['foto'] : $this->member['foto'];
					$avatar = @parse_url ($avatar);
					$avatar = ($avatar['host']) ? $this->member['foto'] : $this->dle_config['http_home_url'] . "uploads/fotos/" . $this->member['foto'];
				} else {
					$avatar = "engine/skins/images/noavatar.png";
				}
			}
		} else {
			if (count(explode("@", $this->member['foto'])) == 2) {
				$avatar = 'http://www.gravatar.com/avatar/' . md5(trim($this->member['foto'])) . '?s=' . intval($this->user_group[$this->member['user_group']]['max_foto']);
			} else {
				$avatar = ($this->member['foto']) ? $this->dle_config['http_home_url'] . "uploads/fotos/" . $this->member['foto'] : "engine/skins/images/noavatar.png";
			}
		}
		
		$pop_notice = $this->member['pm_unread'] ? "<span class=\"badge badge-primary\">{$this->member['pm_unread']}</span>" : "";
		$profile_link = $config['http_home_url'] . "user/" . urlencode ($this->member['name']) . "/";
		
echo <<<HTML
<!doctype html>
	<html lang="ru" dir="ltr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="ie=edge">
			<meta http-equiv="Content-Language" content="ru" />
			<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
			<meta name="apple-mobile-web-app-capable" content="yes">
			<meta name="mobile-web-app-capable" content="yes">
			<meta name="HandheldFriendly" content="True">
			<meta name="MobileOptimized" content="320">
			<title>{$this->module_lang['name']} v{$this->module_config['version']}</title>
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">

			<!-- Dashboard Core -->
			<link href="https://static.punpun.name/new_admin/assets/css/dashboard.css" rel="stylesheet" />
			<link href="https://static.punpun.name/new_admin/assets/css/chosen.min.css" rel="stylesheet" />
			<link href="https://static.punpun.name/new_admin/assets/css/toastr.min.css" rel="stylesheet" />
			<link href="https://static.punpun.name/new_admin/assets/css/jquery.sweet-modal.min.css" rel="stylesheet" />
			<script src="https://static.punpun.name/new_admin/assets/js/vendors/jquery-3.2.1.min.js"></script>
			<script src="https://static.punpun.name/new_admin/assets/js/vendors/bootstrap.bundle.min.js"></script>
			<script src="https://static.punpun.name/new_admin/assets/js/core.js"></script>
	</head>
	<body>
		<div class="page">
			<div class="page-main">
				<div class="header py-4">
					<div class="container">
						<div class="d-flex">
							<a class="header-brand" href="{$PHP_SELF}?mod={$this->module_name}">
								{$this->module_lang['name']}
							</a>
							<div class="d-flex order-lg-2 ml-auto">
								<div class="nav-item d-none d-md-flex">
									<a href="https://punpun.name/doc/easy-filter.html" class="btn btn-sm btn-outline-success" target="_blank">{$this->module_lang['header_doc']}</a>&nbsp;&nbsp;
									<a href="{$PHP_SELF}?mod=options&action=options" class="btn btn-sm btn-outline-primary" target="_blank">{$this->module_lang['header_dle']}</a>&nbsp;&nbsp;
                                    <a href="{$this->dle_config['http_home_url']}" class="btn btn-sm btn-outline-dark" target="_blank">{$this->module_lang['site_link']}</a>
								</div>
								<div class="dropdown">
									<a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
										<span class="avatar" style="background-image: url({$avatar})"></span>
										<span class="ml-2 d-none d-lg-block">
											<span class="text-default">{$this->member['name']}</span>
											<small class="text-muted d-block mt-1">{$this->user_group[$this->member['user_group']]['group_name']}</small>
										</span>
									</a>
									<div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
										<a class="dropdown-item" href="{$profile_link}" target="_blank"><i class="dropdown-icon fe fe-user"></i> {$this->module_lang['header_profile']}</a>
										<a class="dropdown-item" href="{$this->dle_config['http_home_url']}{$this->dle_config['admin_path']}?mod=options&action=personal" target="_blank"><i class="dropdown-icon fe fe-settings"></i> {$this->module_lang['header_settings']}</a>
										<a class="dropdown-item" href="/index.php?do=pm" target="_blank"><span class="float-right">{$pop_notice}</span><i class="dropdown-icon fe fe-mail"></i> {$this->module_lang['header_messages']}</a>
										<a class="dropdown-item" href="{$this->dle_config['http_home_url']}{$this->dle_config['admin_path']}?action=logout"><i class="dropdown-icon fe fe-log-out"></i> {$this->module_lang['header_logout']}</a>
									</div>
								</div>
							</div>
							<a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
								<span class="header-toggler-icon"></span>
							</a>
						</div>
					</div>
				</div>
				<div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
					<div class="container">
						<div class="row align-items-center">
							<div class="col-lg order-lg-first">
								<ul class="nav nav-tabs border-0 flex-column flex-lg-row">
									{$this->menu}
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="my-3 my-md-5">
					<div class="container">
						<div class="row">
							<div class="col-12">
							
HTML;
	}
	
	public function content($action)
	{
		switch ($action) {
			case 'options':
				include __DIR__ . '/options.php';
			break;
            case 'filter':
				include __DIR__ . '/filter.php';
			break;
			default:
				include __DIR__ . '/main.php';
			break;
		}
	}
	
	public function showTr($name, $description, $type, $data)
	{
echo <<<HTML
<tr>
	<td>
		<h6>{$name}</h6>
		<span class="note large">{$description}</span>
	</td>
	<td>
HTML;
		switch ($type) {
			case 'input':
				echo $this->inputForm($data);
			break;
			case 'textarea':
				echo $this->textareaForm($data);
			break;
			case 'checkbox':
				echo $this->checkboxForm($data);
			break;
			case 'radio':
				echo $this->radioForm($data);
			break;
			case 'select':
				echo $this->selectForm($data);
			break;
			case 'select_sort':
				echo $this->select_sortForm($data);
			break;
			default:
				echo $data;
			break;
		}
echo <<<HTML
	</td>
</tr>
HTML;
	}

	public function inputForm($data)
	{
		$input_elemet = $data[3] ? " placeholder=\"{$data[3]}\"" : '';
		$input_elemet .= $data[4] ? ' disabled' : '';
		if ($data[1] == 'range') {
			$class = ' custom-range';
			$input_elemet .= $data[5] ? " step=\"{$data[5]}\"" : '';
			$input_elemet .= $data[6] ? " min=\"{$data[6]}\"" : '';
			$input_elemet .= $data[7] ? " max=\"{$data[7]}\"" : '';
		} elseif ($data[1] == 'number') {
			$class = ' w-9';
			$input_elemet .= $data[5] ? " min=\"{$data[5]}\"" : '';
			$input_elemet .= $data[6] ? " max=\"{$data[6]}\"" : '';
		}
return <<<HTML
	<input type="{$data[1]}" style="float: right;" value="{$data[2]}" class="form-control{$class}" name="{$data[0]}"{$input_elemet}>
HTML;
	}
	
	public function textareaForm($data)
	{
		$input_elemet = $data[2] ? " placeholder=\"{$data[2]}\"" : '';
		$input_elemet .= $data[3] ? ' disabled' : '';
return <<<HTML
	<textarea style="min-height:55px;max-height:150px;min-width:333px;max-width:333px;" class="form-control" name="{$data[0]}"{$input_elemet}>{$data[1]}</textarea>
HTML;
	}
	
	public function radioForm($data)
	{
		$input_elemet = $data[3] ? 'checked' : '';
		$input_elemet .= $data[4] ? 'disabled' : '';
return <<<HTML
	<label class="custom-control custom-radio custom-control-inline">
		<input type="radio" class="custom-control-input" name="{$data[0]}" value="{$data[1]}" {$input_elemet}>
		<span class="custom-control-label">{$data[2]}</span>
	</label>
HTML;
	}
	
	public function select_sortForm($data)
	{
		$opt = false;
		foreach ($data[1] as $key => $val) {
			if (strpos($key, 'xf_') !== false && !$opt) {
				$opt = true;
				$output = "<optgroup label=\"{$this->module_lang[16]}\">" . $output . "</optgroup><optgroup label=\"{$this->module_lang[17]}\">";
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
	
	public function selectForm($data)
	{
		foreach ($data[1] as $key => $val) {
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
		$input_elemet = $data[5] ? ' disabled' : '';
		$input_elemet .= $data[4] ? ' multiple' : '';
		$input_elemet .= $data[6] ? " data-placeholder=\"{$data[6]}\"" : '';
return <<<HTML
	<select name="{$data[0]}" class="form-control custom-select" {$input_elemet}>
		{$output}
	</select>
HTML;
	}
	
	public function checkboxForm($data)
	{
		$input_elemet = $data[1] ? 'checked' : '';
		$input_elemet .= $data[2] ? 'disabled' : '';
return <<<HTML
	<label class="custom-switch">
		<input class="custom-switch-input" type="checkbox" name="{$data[0]}" value="1"{$input_elemet}>
		<span class="custom-switch-indicator"></span>
	</label>
HTML;
	}
	
	public function footerTemplate()
	{
echo <<<HTML
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			function cache() {
				$.sweetModal.defaultSettings.confirm.cancel.label = '{$this->module_lang['cancel']}';
				$.sweetModal.defaultSettings.confirm.yes.label = '{$this->module_lang['confirm']}';
				$.sweetModal.confirm('{$this->module_lang['cache_title']}', '{$this->module_lang['cache_text']}', function() {
					$.post("/engine/mod_punpun/{$this->module_name}/admin/ajax/ajax.php", {action: 'cache'}, function(data) {
						$.sweetModal({
							content: data,
							icon: $.sweetModal.ICON_SUCCESS
						});
						return false;
					});
				});
			}
		</script>
	</body>
</html>
HTML;
	}
}

?>