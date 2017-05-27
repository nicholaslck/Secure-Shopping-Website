function getPriceByPid(pid_s, el_id){
	
	$.ajax({
		url: 'process.php',
		type: 'POST', //send it through post method
		data: {
			action: 'cart_checkout_getPrice',
			pid: parseInt(pid_s),
		},
		dataType: 'text',
		success: function(response) {
			var json = JSON.parse(response.replace('while(1);', ''));
			console.log(json['success']['price']);
			var result = parseInt(json['success']['price']);
			$("#"+el_id).val(result);
		},
		error: function(xhr) {
			console.log(xhr);
		}
	});
}

function cartSubmit() {
	var form = el('cart_form');
	var cart = [];
	storage = window.localStorage.getItem('cart_storage');
	storage = storage ? JSON.parse(storage):{"products":[]};

	for (var i in storage.products) {
		i = parseInt(i);
		var input0 = document.createElement('input');
		input0.type = 'hidden';
		input0.name = 'item_number_'+(i+1);
		input0.value = storage.products[i].pid;
		form.appendChild(input0);
		var input1 = document.createElement('input');
		input1.type = 'hidden';
		input1.name = 'item_name_'+(i+1);
		input1.value = storage.products[i].name;
		form.appendChild(input1);
		var input2 = document.createElement('input');
		input2.type = 'hidden';
		input2.name = 'amount_'+(i+1);
		input2.id = 'auto_price_'+i;
		input2.value = storage.products[i].price;
		form.appendChild(input2);
		getPriceByPid(storage.products[i].pid, input2.id);
		// console.log(input2.value);
		var input3 = document.createElement('input');
		input3.type = 'hidden';
		input3.name = 'quantity_'+(i+1);
		input3.value = storage.products[i].quantity;
		form.appendChild(input3);
		cart.push({"pid":storage.products[i].pid, "quantity":storage.products[i].quantity});
	};

	$.ajax({
		url: 'process.php',
		type: 'POST', //send it through get method
		data: {
			action: 'cart_checkout',
			cart: cart,
		},
		dataType: 'text',
		success: function(response) {
			var json = JSON.parse(response.replace('while(1);', ''));
			// console.log(json['success']);
			var input4 = document.createElement('input');
			input4.type = 'hidden';
			input4.name = 'invoice';
			input4.value = json['success']['invoice'];
			form.appendChild(input4);
			var input5 = document.createElement('input');
			input5.type = 'hidden';
			input5.name = 'custom';
			input5.value = json['success']['custom'];
			form.appendChild(input5);

			// <INPUT type="hidden" name="currency_code" value="HKD">
			var input6 = document.createElement('input');
			input6.type = 'hidden';
			input6.name = 'currency_code';
			input6.value = json['success']['currency'];
			form.appendChild(input6);

			for (var i in storage.products) {
				storage.products.splice(i, 1);
			}

			form.submit();

		},
		error: function(xhr) {
			console.log(xhr);
		}
	});

	// console.log(cart);
	return false;
}

function calculatePriceSum(){
	var value = 0;
	for (var i in storage.products){
		var sPrice = parseFloat(storage.products[i].price);
		var number = parseInt(storage.products[i].quantity);
	
		value = value + sPrice * number;
		// console.log(value);
	}
	el('total_price').innerHTML = "Price: $"+value;
}

window.onload = function updateCart(){
	//Read local storage
	storage = window.localStorage.getItem('cart_storage');
	storage = storage ? JSON.parse(storage):{"products":[]}; //storage is an object now
	//restore shopping list

	// console.log(storage);
	
	// cartSection = []
	// for (var j = 0; j < storage.products.length; j++){
	// 	cartSection.push('<section id="cart_item_pid',parseInt(storage.products[j].pid),'"></section>');
	// }
	// el('cart').innerHTML = cartSection.join('');

	for (var i in storage.products){
		pid = storage.products[i].pid;
		var cartList = [];
		myLib.post({action:'prod_fetchProdDetail_1',pid:pid}, function(json){
			for(var j in storage.products){
				if(json[0].pid == storage.products[j].pid){
					var qty = storage.products[j].quantity;
				}
			}
			cartList.push('<section id="cart_item_pid',parseInt(json[0].pid),'"><p>',json[0].name.escapeHTML(),'</p> <input class="c_qty" type="number" value=',parseInt(qty),'><p id="cprice',parseInt(json[0].pid),'">@ $',parseFloat(json[0].price),'</p></section>');
			el('cart').innerHTML = cartList.join('');
		});
	}
	calculatePriceSum();
}

function ClearLocalStorage(){
	storage = {"products":[]};
	window.localStorage.setItem('cart_storage',JSON.stringify(storage));
	window.location.reload();
}

$(document).on('click', "#clear-cart", function(e){
	ClearLocalStorage();
});

function SaveToLocalStorage(storage){
	// console.log(storage);
	window.localStorage.setItem('cart_storage',JSON.stringify(storage));
}

$(".c_qty").bind('keyup mouseup', function () {
    alert("changed");            
});

$(document).on('click', "button.addBtn", function(e){
	var target = e.target;
	var price = target.previousElementSibling.innerHTML;
	var name = target.previousElementSibling.previousElementSibling.innerHTML;
	var pid = target.parentNode.id.replace(/^prod/, '');
	
	//console.log(price, name);
	var tmp = [];
	tmp.push(el('cart').innerHTML);
	var found = false;
	var found_storage = null;
	for (var i in storage.products){
		if (storage.products[i].pid == pid){
			found = true;
			found_storage = i;
			break;
		}
	}

	if(found == false){
		tmp.push('<section id="cart_item_pid',parseInt(pid),'"><p>',name,'</p> <input type="number" value=',1,'> <p>@ ',price,'</p></section>')
		// console.log(tmp);
		el('cart').innerHTML = tmp.join('');
		// storage.products.push();
		price = price.replace(/\$/, '');
		var new_item = {"pid":pid.toString(), "quantity":"1", "price":parseFloat(price), "name":name.toString()};
		storage.products.push(new_item);
		SaveToLocalStorage(storage);
	}else{
		var cpd = '#cart_item_pid'+pid+' input';
		var value = parseInt($(cpd).attr("value")) + 1;
		$(cpd).attr("value", value);
		// console.log(found_storage);
		storage.products[found_storage].quantity = value.toString();
		SaveToLocalStorage(storage);
	}
	calculatePriceSum();
});













