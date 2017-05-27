<?php 
include_once('lib/db_inc.php');
include_once('../auth.php');
include_once('../lib/csrf.php');
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Shop - Admin Panel</title>
	<link href="incl/admin.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<h1>IERG4210 Shop - Admin Panel 	user: <?php echo $user_email;?></h1>
<div>
	<form method='POST' action="../auth-process.php?action=logout">
		<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('logout'); ?>" />
		<input type="submit" value="Logout" />	
	</form>
</div>
<article id="main">

<section id="categoryPanel">
	<fieldset>
		<legend>New Category</legend>
		<form id="cat_insert" method="POST" action="admin-process.php?action=cat_insert" onsubmit="return false;">
			<label for="cat_insert_name">Name</label>
			<div><input id="cat_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('cat_insert'); ?>" />

			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
	<!-- Generate the existing categories here -->
	<ul id="categoryList"></ul>
</section>

<section id="categoryEditPanel" class="hide">
	<fieldset>
		<legend>Editing Category</legend>
		<form id="cat_edit" method="POST" action="admin-process.php?action=cat_edit" enctype="multipart/form-data" onsubmit="return false;">
			<label for="cat_edit_name">Name</label>
			<div><input id="cat_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" id="cat_edit_catid" name="catid" />
			<input type="submit" value="Submit" /> <input type="button" id="cat_edit_cancel" value="Cancel" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('cat_edit'); ?>" />
		</form>
	</fieldset>
</section>

<section id="productPanel">
	<fieldset>
		<legend>New Product</legend>
		<form id="prod_insert" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data" >
			<label for="prod_insert_catid">Category *</label>
			<div><select id="prod_insert_catid" required="true" name="catid"></select></div>

			<label for="prod_insert_name">Name *</label>
			<div><input id="prod_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<label for="prod_insert_price">Price *</label>
			<div><input id="prod_insert_price" type="number" step="any" name="price" required="true" pattern="^[\d\.]+$" /></div>

			<label for="prod_insert_description">Description</label>
			<div><textarea id="prod_insert_description" name="description" pattern="^[\w\-,\?\!\.\'\" ]*$"></textarea></div>

			<label for="prod_insert_name">Image *</label>
			<div><input type="file" name="file" required="true" accept="image/jpeg,image/gif,image/png" /></div>

			<input type="submit" value="Submit" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('prod_insert'); ?>" />
		</form>
	</fieldset>
	
	<!-- Generate the corresponding products here -->
	<ul id="productList"></ul>
</section>

<section id="productEditPanel" class="hide">
    <fieldset>
		<legend>EditProduct</legend>
		<form id="prod_edit" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data"> 
			<input type="hidden" id="prod_edit_pid" name="pid" />

			<label for="prod_edit_catid">Category *</label>
			<div><select id="prod_edit_catid" required="true" name="catid"></select></div>

			<label for="prod_edit_name">Name *</label>
			<div><input id="prod_edit_name" type="text" required="true" name="name" pattern="^[\w\- ]+$" /></div>

			<label for="prod_edit_price">Price *</label>
			<div><input id="prod_edit_price" type="number" required="true" name="price" pattern="^[\d\.]+$" /></div>

			<label for="prod_edit_description">Description </label>
			<div><textarea id="prod_edit_description" name="description" pattern="^[\w\-,\?\!\.\'\" ]*$"></textarea></div>

			<label for="prod_edit_name">Image</label>
			<div><input type="file" name="file" accept="image/jpeg,image/gif,image/png" /></div>

			<input type="submit" value="Submit" /> <input type="button" id="prod_edit_cancel" value="Cancel" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('prod_edit'); ?>" />
		</form>
	</fieldset>
</section>

<section id="userEditPanel">
	<fieldset>
		<legend>Add user</legend>
		<form method="POST" action="admin-process.php?action=user_create">
			<div>
				<label for="email">Email: </label>
				<input id="email" type="text" name="email" required="true" />
			</div>
			<div>
				<label for="password">Password: </label>
				<input id="password" type="password" name="password" required="true" />
			</div>
			<div>
				<input type="submit" value="Submit" />
				<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('user_create'); ?>" />
			</div>
		</form>	
	</fieldset>
</section>

<div class="clear"></div>
</article>
<script type="text/javascript" src="incl/myLib.js"></script>
<script type="text/javascript"> 
	(function(){
		function updateUI() {
			myLib.get({action:'cat_fetchall'}, function(json){
				// loop over the server response json
				//   the expected format (as shown in Firebug): 
				for (var options = [], listItems = [],
						i = 0, cat; cat = json[i]; i++) {
					options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
					listItems.push('<li id="cat' , parseInt(cat.catid) , '"><span class="name">' , cat.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
				}
				// console.log(listItems);
				el('prod_edit_catid').innerHTML = '<option></option>' + options.join('');
				el('prod_insert_catid').innerHTML = '<option></option>' + options.join('');
				el('categoryList').innerHTML = listItems.join('');
			});
			el('productList').innerHTML = '';
		}
		updateUI();
		
		el('categoryList').onclick = function(e) {
			if (e.target.tagName != 'SPAN')
				return false;
			
			var target = e.target,
				parent = target.parentNode,
				id = target.parentNode.id.replace(/^cat/, ''),
				name = target.parentNode.querySelector('.name').innerHTML;
			
			// handle the delete click
			if ('delete' === target.className) {
				confirm('Sure?') && myLib.post({action: 'cat_delete', catid: id}, function(json){
					alert('"' + name + '" is deleted successfully!');
					updateUI();
				});
			
			// handle the edit click
			} else if ('edit' === target.className) {
				// toggle the edit/view display
				el('categoryEditPanel').show();
				el('categoryPanel').hide();
				
				// fill in the editing form with existing values
				el('cat_edit_name').value = name;
				el('cat_edit_catid').value = id;
			
			//handle the click on the category name
			} else {

				// populate the product list or navigate to admin.php?catid=<id>

				//el('productList').innerHTML = '<li> Product 1 of "' + name + '" [Edit] [Delete]</li><li> Product 2 of "' + name + '" [Edit] [Delete]</li>';

				myLib.post({action: 'prod_fetchFromCatid', catid: id}, function(json){
					el('prod_insert_catid').value = id;
				// loop over the server response json
				//   the expected format (as shown in Firebug): 
					for (var listItems = [], i = 0, prod; prod = json[i]; i++) {
							listItems.push('<li id="prod' , parseInt(prod.pid) , '"><span class="name">' , prod.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
					}
					// console.log(listItems);
					el('productList').innerHTML = listItems.join('');
				});
			}
		}

		el('productList').onclick = function(e) {
			if (e.target.tagName != 'SPAN')
				return false;
			var target = e.target,
				parent = target.parentNode,
				id = target.parentNode.id.replace(/^prod/, ''),
				name = target.parentNode.querySelector('.name').innerHTML;


			if ('delete' === target.className) {
				confirm('Sure?') && myLib.post({action: 'prod_delete', pid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
				});
			} else if ('edit' === target.className) {
				// toggle the edit/view display
				el('productEditPanel').show();
				el('productPanel').hide();
				
				// fill in the editing form with existing values
				el('prod_edit_name').value = name;
				el('prod_edit_pid').value = id;

				myLib.post({action: 'prod_fetchProdDetail', pid: id}, function(json){
					el('prod_edit_catid').value = parseInt(json[0].catid);
					el('prod_edit_description').value = json[0].description.escapeHTML();
					el('prod_edit_price').value = parseFloat(json[0].price); 
				});
			//handle the click on the category name
			}
		}
		
		
		el('cat_insert').onsubmit = function() {
			return myLib.submit(this, updateUI);
		}
		el('cat_edit').onsubmit = function() {
			return myLib.submit(this, function() {
				// toggle the edit/view display
				el('categoryEditPanel').hide();
				el('categoryPanel').show();
				updateUI();
			});
		}
		el('cat_edit_cancel').onclick = function() {
			// toggle the edit/view display
			el('categoryEditPanel').hide();
			el('categoryPanel').show();
		}

		el('prod_edit_cancel').onclick = function() {
			// toggle the edit/view display
			el('productEditPanel').hide();
			el('productPanel').show();
		}

	})();
</script>



</body>
</html>
