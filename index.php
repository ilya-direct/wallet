<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
<!--	<link rel="stylesheet" href="css/bootstrap.min.css">-->
<!--	<script type="text/javascript" src="js/jquery.min.js"></script>-->
<!--	<script type="text/javascript" src="js/bootstrap.min.js"></script>-->
	<script type="text/javascript" src="js/yui.js"></script>
	<title>Электронная СР</title>
</head>

<body>
<div id="first_div"></div>
<div>
	<ul>
		<li>Ссылка 1</li>
		<li>Ссылка 2</li>
		<li>Ссылка 3</li>
		<li>Ссылка 4</li>
		<li>Ссылка 5</li>
	</ul>
</div>
<p id="first_paragraph">Нажми на меня!</p>
</body>

<script type="text/javascript">
YUI.add('module1',function(Y){
	 Y.set_hello_in_div=function(){
		 var node = Y.one('#first_div');
		 node.setHTML("<p>Hello World!</p>");
	 }
	 //this.set_hello();
},'0.0.1',{requires:['node']});
YUI.add('module2',function(Y){
	 Y.set_hello_in_div=function(){
		 var node = Y.one('#first_div');
		 node.setHTML("<p>Hello World 2!</p>");
	 }
	 //this.set_hello();
},'0.0.1',{requires:['node']});
YUI().use('module2','module1',function(Y,msg){
	console.log(msg);
	Y.set_hello_in_div();
});
</script>


