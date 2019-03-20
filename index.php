<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Dancing with Death</title>
	<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">	
</head>
<body>
	<div class="container">
		<div class="jumbotron text-center">
		  <h1 class="display-4">Dancing with Death</h1>
		  <p class="lead"><img src="//rlv.zcache.com/dance_with_death_postcard-re37ab8bac8af41dcbc857ad4d0edd397_vgbaq_8byvr_540.jpg" class="img-fluid"></p>
		  <hr class="my-4">		  
		</div>
		<form id="booking" method="post" enctype="application/x-www-form-urlencoded" action="v1/appointment">
		  <div class="form-group">
			<label for="inputEmail1">Email address</label>
			<input type="email" class="form-control" id="inputEmail1" name="email" aria-describedby="emailHelp" placeholder="Enter email" required>
			<small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
		  </div>		  
		  <div class="form-group" style="position: relative">
			<label for="inputDate">Schedule Date</label>
			<input type="text" id="inputDate" class="form-control" name="date" placeholder="Select Date">
		  </div>
		  <button type="submit" class="btn btn-primary">Submit</button>
		</form>
		<hr>
		<div class="row" id="agenda">			
		</div>
		<hr><hr>
	</div>
	<!-- alerta -->
	<div class="modal" id="alert" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Alerta</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		  </div>		  
		</div>
	  </div>
	</div>
	<!-- script -->
	<link href="assets/css/bootstrap-glyphicons.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="assets/datepicker/css/bootstrap-datetimepicker.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" crossorigin="anonymous"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js"></script>
	<script src="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="assets/datepicker/js/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript">
	$(function() {
		$('#inputDate').datetimepicker({
			daysOfWeekDisabled:[0,6],
			minuteStep:1
		});
		$('#booking').ajaxForm({ 
			dataType:  'json', 
			beforeSubmit: function(){
				$('#booking button[type="submit"]').prop('disabled', true);
			},
			success:   function(response) {
				console.log(response);
				alerta(response.return);
				$('#booking button[type="submit"]').prop('disabled', false);
			}
		});
		$( '#booking input[name="email"]' ).blur(function() {
			booking();
		});
	});
	function booking(){
		$.ajax({
			data:  {email:$('#booking input[name="email"]').val()}, //datos que se envian a traves de ajax
			url:   'v1/appointments', //archivo que recibe la peticion
			method :  'GET', //método de envio
			dataType: 'json',
			beforeSend: function () {
				
			},
			success:  function (response) {
				console.log(response);
				if(response.error){
					alerta(response.return);	
				}else{
					$('#agenda .dates').remove();
					$.each(response.booking, function( index, value ) {
						var fecha = new Date(value.date*1000);
						$('#agenda').append('<button type="button" class="btn btn-primary" onClick="delDate('+value.id+')">Appointment <span class="badge badge-light">'+fecha+'</span></button>').addClass('dates');
					});
				}
			}
        });
	}
	function delDate($id){
		$.ajax({
			data:  {id:$id}, //datos que se envian a traves de ajax
			url:   'v1/appointment', //archivo que recibe la peticion
			method :  'DELETE', //método de envio
			dataType: 'json',
			beforeSend: function () {
				
			},
			success:  function (response) {
				console.log(response);
				if(!response.error){
					booking();
				}
				alerta(response.return);
			},
			error:  function (xhr, ajaxOptions, thrownError) {
				/*
				console.log(xhr)
				console.log(ajaxOptions)*/
				alerta('Error:'+thrownError);
			}
        });
	}
	function alerta($msj){
		$('#alert .modal-body').empty().html($msj);
		$('#alert').modal('show');
	}
	</script>
</body>
</html>