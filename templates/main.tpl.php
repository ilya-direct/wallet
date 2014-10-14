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
	<script type="text/javascript" src="js/calendar.js"></script>
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
				</thead>
						<tbody>		</tbody>
			</table>
		</div>
		<div class="span9">
			<table class="table">
				<?php foreach ($TPL->table as $row): ?>
					<tr>
						<td>
							<?= $row['sign'] ?>
						</td>
						<td>
							<img src="/images/<?= $row['card'] ?>_icon.jpg">
						</td>
						<td>
							<?= $row['sum'] ?>
						</td>
						<td>
							<?= $row['item'] ?>
						</td>
					</tr>
				<?php endforeach; ?>
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