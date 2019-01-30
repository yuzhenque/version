	//ajax取出市
	function getCity(id){
			$.ajax({
				   type: "POST",
				   url: "/shop_admin.php/BusinessShop/city",
				   data: "id="+id,
				   success: function(msg){
					 $("#city").empty();  
				     $("#city").append(msg);
				   }
			});
	}