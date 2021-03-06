# Easy Filter
[![Documentation](https://img.shields.io/badge/Documentation-Link-blue.svg?style=flat-square)](https://punpun.name/doc/easy-filter.html)
![version](https://img.shields.io/badge/version-2.0.2-green.svg?style=flat-square "Version")
![DLE](https://img.shields.io/badge/DLE-13.x-red.svg?style=flat-square "DLE Version")
[![CC BY-NC-SA License](https://img.shields.io/badge/license-CC_BY--NC--SA_3.0-blue.svg?style=flat-square)](https://github.com/punpun1/EasyFilter/blob/master/LICENSE)

**Модуль Easy Filter** поможет реализовать фильтр по дополнительным полям. Модуль полностью работает на AJAX, никакой перезагрузки страницы. А так же имеет базовые настройки.
# Требования к системе
* Версия DLE: 13.0 и выше
* Поддерживаемая кодировка: UTF-8
* Версия php: 5.4 и выше

# Установка модуля Easy Filter
1. Загрузить архив с модулем через меню Утилиты - Управления плагинами.
2. В шаблоне сайта **main.tpl** перед тегом 

		</head>
  
вставить: 

		<link rel="stylesheet" href="{THEME}/mod_punpun/easy_filter/css/easy_filter.css">
  
и перед тегом:
 
		</body>
  
вставить:   

		<script src="{THEME}/mod_punpun/easy_filter/js/easy_filter.js"></script>


Далеее  в месте вывода фильтра в шаблон добавить код: 

	<form id="punpun_filter">
			<div class="filter-wrap" id="filter-wrap">
					<div class="filter-box">
							{include file="engine/mod_punpun/easy_filter/site/filter.php"}
					</div>
			</div>
	</form>


3. В админке модуля /admin.php?mod=easy_filter активируйте вывод необходимых полей.


# Лицензия:
Данное программное обеспечение издается по лицензии CC Attribution — Noncommercial — Share Alike.<br/>
<b>Вы можете свободно:</b><ul><li>Делиться (обмениваться) — копировать и распространять материал на любом носителе и в любом формате</li>
<li>Адаптировать (создавать производные материалы) — делать ремиксы, видоизменять, и создавать новое, опираясь на этот материал</li>
<li>Лицензиар не вправе аннулировать эти свободы пока вы выполняете условия лицензии.</li>
</ul>
<b>При обязательном соблюдении следующих условий:</b><ul>
<li>«Attribution» («Атрибуция») — Вы должны обеспечить соответствующее указание авторства, предоставить ссылку на лицензию, и обозначить изменения, если таковые были сделаны. Вы можете это делать любым разумным способом, но не таким, который подразумевал бы, что лицензиар одобряет вас или ваш способ использования произведения.</li>
<li>«NonCommercial» («Некоммерческое использование») — Вы не вправе использовать этот материал в коммерческих целях.</li>
<li>«ShareAlike» («На тех же условиях») — Если вы перерабатываете, преобразовываете материал или берёте его за основу для производного произведения, вы должны распространять переделанные вами части материала на условияхтой же лицензии, в соответствии с которой распространяется оригинал.</li>
<li>Без дополнительных ограничений — Вы не вправе применять юридические ограничения или технологические меры, создающие другим юридические препятствия в выполнении чего-либо из того, что разрешено лицензией.</li>
</ul>
