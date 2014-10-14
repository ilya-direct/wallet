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
	<div class="row">
		<div class="span4">
			<!--Sidebar content-->werwwerewrwere
		</div>
		<div class="span8">
			<table class="table">
				<?php foreach($TPL->table as $row): ?>
					<tr>
						<td>
							<?=$row['sign'] ?>
						</td>
						<td>
							<img src="/images/<?=$row['card']?>_icon.jpg" >
						</td>
						<td>
							<?=$row['sum'] ?>
						</td>
						<td>
							<?=$row['item'] ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
</body>
</html>