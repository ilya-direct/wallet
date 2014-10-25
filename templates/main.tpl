<!DOCTYPE html>
<html>
<head>
	<title>
		Таблица расходов
	</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/style.css">
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
	<div class="row" id="header">
		<div class="span12">
			Header
		</div>
	</div>
	<div class="row" id="content">
		<div class="span3">
			Календарь

			<table id="calendar2">
				<thead>
				<tr><td>‹<td colspan="5"><td>›
				<tr><td>Пн<td>Вт<td>Ср<td>Чт<td>Пт<td>Сб<td>Вс
						<tbody>
			</table>
		</div>
		<div class="span9">
			<table class="table table-striped table-bordered table-hover" id="main-table">
				<thead>
				<tr>
					<th>Знак</th>
					<th>Счёт</th>
					<th>Сумма</th>
					<th>Элемент</th>
				</tr>
				</thead>
				<tbody>
                {foreach from=$table item=row}
					<tr>
						<td>{$row['sign']}</td>
						<td><img src="/images/{$row['card']}_icon.jpg"></td>
						<td>{$row['sum']}</td>
						<td>{$row['item']}</td>
					</tr>
				{/foreach}
				<form class="form-inline">
					<?php for($i=0; $i<3;$i++): ?>
					<tr>
						<td>
							<select class="span1">
								<option>-</option>
								<option>+</option>
							</select>
						</td>
						<td>
							<select class="span1">
							{foreach from=$cards item=card}
								<option><img src="/images/{$card['name']}_icon.jpg"></option>
							{/foreach}
							</select>
						</td>
						<td><input type="text" class="input-small"></td>
						<td><input type="text" class="input-small" ></td>
					</tr>
					<?php endfor; ?>
					<tr><button class="btn btn-primary" type="button">Сохранить</button></tr>
				</form>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row" id="footer">
		<div class="span12" >
			футер
		</div>
	</div>
</div>
</body>
</html>
<script type="text/javascript" src="js/calendar.js"></script>