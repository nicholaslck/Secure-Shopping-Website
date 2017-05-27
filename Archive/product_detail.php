<html>
	<head>
		    <?php readfile('shared/header.html');?>
		    <title>Product Details</title>
	</head>
	<body>
		<section class="nav-top">
		    <?php readfile('shared/nav-top_view.html');?>
		</section>

		<section class="nav-left">
			<?php readfile('shared/nav-left_view.html');?>
		</section>

		<section class="container">
			<section id="productList">
			<section id="directory">
			</section>
			<section>
				<article class="product">
					<section class="picture" id="picture">
						<
					</section>
					<section class="details" id="details">
					</section>
				</article >
			</section>

			</section>
			<hr />
			<?php readfile('shared/footer.html');?>
		</section>

		<article>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
			<script src="js/index-contanier.js" type="text/javascript"></script>
			<script src="js/myLib.js" type="text/javascript" ></script>
			<script type="text/javascript">
				(function(){
					function updateUI() {
						var pid=<?php 
							$_GET['pid'] = (int)$_GET['pid'];
							echo $_GET['pid'];
						?>;
						directorylist = [];

						myLib.get({action:'cat_fetchall'}, function(json){
							// loop over the server response json
							//   the expected format (as shown in Firebug): 
							for (var options = [], listItems = [],
									i = 0, cat; cat = json[i]; i++) {
								// options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
								listItems.push('<a class="name" id="cat' , parseInt(cat.catid) , '" href="index.php?catid=',parseInt(cat.catid),'">' , cat.name.escapeHTML() , '</a>');

								var selectedCat = <?php $_REQUEST['catid'] = (int)$_REQUEST['catid'];
								echo $_REQUEST['catid'];?>;
								if(parseInt(cat.catid) == selectedCat){
									directorylist.push('<h4><a href="index.php">Home</a> > <a href="index.php?catid=',parseInt(json[0].catid)+'">', cat.name ,'</a> > <a>');
								}
							}
							// console.log(listItems);

							el('nav-left').innerHTML = listItems.join('');
						});

						myLib.post({action: 'prod_fetchProdDetail', pid: parseInt(pid)}, function(json){
							
							var name = json[0].name.escapeHTML(), price = parseFloat(json[0].price), description = json[0].description.escapeHTML();
							el('picture').innerHTML = '<img src="img/'+ parseInt(pid) +'.jpg" class="thumbnail">';
							el('details').innerHTML = '<h2>' + name + '</h2><p>$' + parseFloat(price) + '</p><button class="addBtn">Add</button><p>Descriptions: ' + description.escapeHTML() + '</p>';
							var pd = "prod" + pid.toString();
							el('details').setAttribute("id", pd);

							directorylist.push( name,'</a></h4>');
							el('directory').innerHTML = directorylist.join('');
						});
					}
					updateUI();

					el('nav-left').onclick = function(e){
						if (e.target.tagName != 'A')
							return false;
						var target = e.target, id = target.id.replace(/^cat/, ''), name = target.innerHTML;
					}
				})();
			</script>
			<script src="js/shoppingcart.js" type="text/javascript"></script>
		</article>
	</body>
</html>
