<?php
define('EXEC',1);
require_once(__DIR__.'/config.php');
$DB=mysqli::get_instance();

if(optional_param('request',false,PARAM_BOOL)){
	require_once('lib/rename_items.php');
}
$item=$DB->get_record_sql('select * from item where correct_item_name_id is null order by name limit 1');

$assigned_items=
	$DB->get_records_sql('select it.name as item_name,cit.name as assigned from item it
	inner join correct_item_name cit on cit.id=it.correct_item_name_id where it.name!=cit.name order by it.name')
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		.search_area{
			width: 350px;
			margin: 0px;
			position: relative;
		}

		#search_box{
			width:200px;
			padding:2px;
			margin:1px;
			border:1px solid #000;
		}

		#search_advice_wrapper{
			display:none;
			width: 350px;
			background-color: rgb(80, 80, 114);
			color: rgb(255, 227, 189);
			-moz-opacity: 0.95;
			opacity: 0.95;
			-ms-filter:"progid:DXImageTransform.Microsoft.Alpha"(Opacity=95);
			filter: progid:DXImageTransform.Microsoft.Alpha(opacity=95);
			filter:alpha(opacity=95);
			z-index:999;
			position: absolute;
			top: 24px; left: 0px;
		}

		#search_advice_wrapper .advice_variant{
			cursor: pointer;
			padding: 5px;
			text-align: left;
		}
		#search_advice_wrapper .advice_variant:hover{
			color:#FEFFBD;
			background-color:#818187;
		}
		#search_advice_wrapper .active{
			cursor: pointer;
			padding: 5px;
			color:#FEFFBD;
			background-color:#818187;
		}

	</style>
	<!--	<link rel="stylesheet" href="css/bootstrap.min.css">-->
		<script type="text/javascript" src="js/jquery.min.js"></script>
	<!--	<script type="text/javascript" src="js/bootstrap.min.js"></script>-->
<!--	<script type="text/javascript" src="js/yui.js"></script>-->
	<script type="text/javascript">
		var suggest_count = 0;
		var input_initial_value = '';
		var suggest_selected = 0;

		$(window).load(function(){
			// читаем ввод с клавиатуры
			$("#search_box").keyup(function(I){
				// определяем какие действия нужно делать при нажатии на клавиатуру
				switch(I.keyCode) {
					// игнорируем нажатия на эти клавишы
					case 13:  // enter
					case 27:  // escape
					case 38:  // стрелка вверх
					case 40:  // стрелка вниз
						break;

					default:
						// производим поиск только при вводе более 2х символов
						if($(this).val().length>1){

							input_initial_value = $(this).val();
							// производим AJAX запрос к /ajax/ajax.php, передаем ему GET query, в который мы помещаем наш запрос
							$.get("/request.php", { "action":"search","str":$(this).val() },function(data){
								//php скрипт возвращает нам строку, ее надо распарсить в массив.
								// возвращаемые данные: ['test','test 1','test 2','test 3']
								var list = eval("("+data+")");
								suggest_count = list.length;
								if(suggest_count > 0){
									// перед показом слоя подсказки, его обнуляем
									$("#search_advice_wrapper").html("").show();
									for(var i in list){
										if(list[i] != ''){
											// добавляем слою позиции
											$('#search_advice_wrapper').append('<div class="advice_variant">'+list[i]+'</div>');
										}
									}
								}
							}, 'html');
						}
						break;
				}
			});

			//считываем нажатие клавишь, уже после вывода подсказки
			$("#search_box").keydown(function(I){
				switch(I.keyCode) {
					// по нажатию клавишь прячем подсказку
					case 13: // enter
					case 27: // escape
						$('#search_advice_wrapper').hide();
						return false;
						break;
					// делаем переход по подсказке стрелочками клавиатуры
					case 38: // стрелка вверх
					case 40: // стрелка вниз
						I.preventDefault();
						if(suggest_count){
							//делаем выделение пунктов в слое, переход по стрелочкам
							key_activate( I.keyCode-39 );
						}
						break;
				}
			});

			// делаем обработку клика по подсказке
			$('.advice_variant').live('click',function(){
				// ставим текст в input поиска
				$('#search_box').val($(this).text());
				// прячем слой подсказки
				$('#search_advice_wrapper').fadeOut(350).html('');
			});

			// если кликаем в любом месте сайта, нужно спрятать подсказку
			$('html').click(function(){
				$('#search_advice_wrapper').hide();
			});
			// если кликаем на поле input и есть пункты подсказки, то показываем скрытый слой
			$('#search_box').click(function(event){
				//alert(suggest_count);
				if(suggest_count)
					$('#search_advice_wrapper').show();
				event.stopPropagation();
			});
		});

		function key_activate(n){
			$('#search_advice_wrapper div').eq(suggest_selected-1).removeClass('active');

			if(n == 1 && suggest_selected < suggest_count){
				suggest_selected++;
			}else if(n == -1 && suggest_selected > 0){
				suggest_selected--;
			}

			if( suggest_selected > 0){
				$('#search_advice_wrapper div').eq(suggest_selected-1).addClass('active');
				$("#search_box").val( $('#search_advice_wrapper div').eq(suggest_selected-1).text() );
			} else {
				$("#search_box").val( input_initial_value );
			}
		}
	</script>
	<title>Корректировка элементов</title>

</head>
<body>
	<table>
	<? foreach($assigned_items as $assigned): ?>
		<tr>
			<td>
				<?=$assigned->item_name?>
			</td>
			<td>=></td>
			<td>
				<?=$assigned->assigned?>
			</td>
		</tr>
	<? endforeach;?>
	</table>
	<div class="search_area">
		<form action='#' method="post" accept-charset="utf-8">
			<input type='hidden' name='item_id' value='<?=$item->id?>'>
			<input type='hidden' name='request' value=1>
			"<?=$item->name?>" <input id='search_box' type='text' name='item_name' value='<?=$item->name?>'>
			<input type='submit' name='submit' value='Сохранить'>
			<div id="search_advice_wrapper"></div>
		</form>
	</div>
</body>


