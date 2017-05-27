(function(){
	function updateUI() {
		myLib.get({action:'cat_fetchall'}, function(json){
			// loop over the server response json
			//   the expected format (as shown in Firebug): 
			for (var options = [], listItems = [],
					i = 0, cat; cat = json[i]; i++) {
				// options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
				listItems.push('<a class="name" id="cat' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</a>');
			}
			// console.log(listItems);
			el('nav-left').innerHTML = listItems.join('');
		});
	}
	updateUI();

	el('nav-left').onclick = function(e){
		if (e.target.tagName != 'A')
			return false;
		var target = e.target, id = target.id.replace(/^cat/, ''), name = target.innerHTML;

		myLib.post({action: 'prod_fetch1FromCatid', catid: parseInt(id)}, function(json){

			var directory = '<section><h4><a href="index.php">Home</a> > ' + name.escapeHTML() + '</h4></section>';

			for (var product = [], i = 0, prod; prod = json[i]; i++) {
				product.push('<article class="floating-box" id="prod', parseInt(prod.pid), '"><a href="product_detail.php?action=prod_fetchProdDetail&pid=',parseInt(prod.pid),'&catid=',parseInt(prod.catid),'"><img src="img/', parseInt(prod.pid), '.jpg" class="thumbnail"></a><h4>', prod.name.escapeHTML(), '</h4><p>$', parseFloat(prod.price), '</p><button class="addBtn">Add</button></article>');
			}

			// console.log(listItems);
			el('productList').innerHTML = directory + product.join('');
		});
	}	
})();
