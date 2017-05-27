<!DOCTYPE html>
<html>
	<head>
		    <?php readfile('shared/header.html');?>
		    <title>Main Page</title>
	</head>
	<body>
		<section class="nav-top">
		    <?php readfile('shared/nav-top_view.html');?>
		</section>

		<section class="nav-left">
			<?php readfile('shared/nav-left_view.html');?>
		</section>

		<section class="container">
			<?php readfile('categories.html');?>
			<hr />
			<?php readfile('shared/footer.html');?>
		</section>

		<article>
			<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
			<script src="js/myLib.js" type="text/javascript" ></script>
			<script src="js/index-contanier.js" type="text/javascript"></script>
			<script type="text/javascript">
				myLib.post({action: 'prod_fetch1FromCatid', catid:<?php 
					if($_REQUEST['catid'] != null){
						$_REQUEST['catid'] = (int)$_REQUEST['catid'];
						echo $_REQUEST['catid'];
					}else{
						echo 1;
					}?>}, function(json){

				var directory = '<section><h4><a href="index.php">Home</a> > ' + 'phones' + '</h4></section>';

				for (var product = [], i = 0, prod; prod = json[i]; i++) {
					product.push('<article class="floating-box" id="prod', parseInt(prod.pid), '"><a href="product_detail.php?action=prod_fetchProdDetail&pid=',parseInt(prod.pid),'&catid=',parseInt(prod.catid),'"><img src="img/', parseInt(prod.pid), '.jpg" class="thumbnail"></a><h4>', prod.name.escapeHTML(), '</h4><p>$', parseFloat(prod.price), '</p><button class="addBtn">Add</button></article>');
				}

				// console.log(listItems);
				el('productList').innerHTML = directory + product.join('');
				}); 
			</script>
			<script src="js/index-process.js" type="text/javascript"></script>
			<script src="js/shoppingcart.js" type="text/javascript"></script>
		</article>
	</body>
</html>