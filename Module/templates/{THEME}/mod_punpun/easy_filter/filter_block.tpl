[year]
	<div class="fb-sect-name">Год</div>
	<div class="fb-sect">
		{year-value}
	</div>
[/year]
[janre]
	<div class="fb-sect-name">Выберите жанр</div>
	<div class="fb-sect fb-sel">
		<select name="janre" multiple data-placeholder="Выберите жанр">
			<option value=""> - </option>
			{janre-value}
		</select>
	</div>
[/janre]

<div class="fb-sect-name">Выберите сортировку</div>
<div class="fb-sect fb-sel">
	<select name="order_by" multiple data-placeholder="Выберите сортировку">
		<option value=""> - </option>
		<option value="title"> по названию новости </option>
		<option value="dec_year"> по году новости </option>
	</select>
</div>

<div class="fb-sect-name">Выберите тип сортировки</div>
<div class="fb-sect fb-sel">
	<select name="order" data-placeholder="Выберите тип сортировки">
		<option value="desc"> По убыванию </option>
		<option value="asc"> По возрастанию </option>
	</select>
</div>