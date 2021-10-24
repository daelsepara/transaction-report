
<!DOCTYPE html>
<html lang="{{str_replace('_', '-', app()->getLocale())}}">

@include('header', ['page_title' => "{$page_title}"])

<body>
    <div class = "containter">
        <div class = "container">
			<br/>
            <div class = "row">
				<div class = "col-md-4">
					<div class = "dropdown">
						<button class = "btn btn-primary dropdown-toggle" type = "button" id="tools-menu" data-toggle="dropdown" data-bs-auto-close = "true">
							Tools
						</button>
						<ul class = "dropdown-menu" role="menu" aria-labelledby="tools-menu" bg-light>
							<li class = "dropdown-header">Available Tools</li> 
							<li><a class = "dropdown-item" href="/commissions/top100">Top 100 Distributors</a></li>
							<li><hr class = "dropdown-divider"/></li>
							<li class = "dropdown-header">Go back to the main page</li> 
							<li><a class = "dropdown-item" href="/commissions">Back</a></li>
						</ul>
					</div>
                </div>
            </div>
            <hr class = "border border-primary"/>
            <div class = "row">
				<div class = "col-md-4">
					<h1 class = "text-primary">{{$page_title}}</h1>
                </div>
            </div>
            <hr class = "border border-primary"/>
            <form method = "post" action = "/commissions/transactions#report">
			@csrf
			<div class = "row">
				<div class = "col-md-4">
					<b>Distributor</b>
				</div>
			</div>
            <div class = "row">
				<div class = "col-md-4">
					<input type = "text" class = "form-control typeahead" placeholder = "Search by ID, Username, First Name, Last Name" name = "distributor" id = "distributor" value = "{{$distributor}}" autocomplete="off">
				</div>
            </div>
			<br/>
            <div class = "row">
				<div class = "col-md-4">
					<b>Date</b>
				</div>
			</div>
            <div class = "row">
				<div class = "col-md-4">
					<input type = "text" class = "form-control datepicker" name = "startDate" id = "startDate" value = "{{$startDate}}" data-provide="datepicker">
				</div>
				<div class = "col-auto">to</div>
				<div class = "col-md-4">
					<input type = "text" class = "form-control datepicker" name = "endDate" id = "endDate" value = "{{$endDate}}" data-provide="datepicker">
				</div>
            </div>
            <br/>
            <div class = "row">
				<div class = "col-md-4">
					<button type = "submit" class = "btn btn-primary mb-2">Filter</button>
				</div>
			</div>
			<input type="hidden" name="_token" value="{{csrf_token()}}">
            </form>
            <br/>
			<div class = "row">
				<div class = "col-md-8">
					<h4><b><span class = "text-secondary">Total Commissions: </span><span class = "text-success">$ {{number_format($commissions, 2)}}</span></b></h4>
				</div>
			</div>
			<a name = 'report'/>
            <?php if (count($transactions) > 0): ?>
            <div class = "row">
				<div class = "col-auto">
				<table class = "table table-striped table-bordered">
					<thead class = "thead-dark">
						<tr>
							<th scope="col">Invoice</th>
							<th scope="col">Purchaser</th>
							<th scope="col">Distributor</th>
							<th scope="col">Referred Distributors</th>
							<th scope="col">Order Date</th>
							<th scope="col">Order Total</th>
							<th scope="col">Percentage</th>
							<th scope="col">Commissions</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>
						<?php $page_total = 0; ?>
						<?php foreach($transactions as $transaction): ?>
						<tr>
							<th scope="col">{{$transaction->Invoice}}</th>
							<th scope="col">{{$transaction->Purchaser}}</th>
							<th scope="col">{{$transaction->Distributor}}</th>
							<th scope="col" class = "text-center">{{$transaction->ReferredDistributors > 0 ? $transaction->ReferredDistributors : ''}}</th>
							<th scope="col">{{$transaction->OrderDate}}</th>
							<th scope="col"  class = "text-right">$ {{number_format($transaction->OrderTotal, 2)}}</th>
							<?php
								$percentage = 0; 
								
								if (strlen(trim($transaction->Distributor)) > 0)
								{ 
									if ($transaction->ReferredDistributors < 5)
									{
										$percentage = 5; 
									}
									else if ($transaction->ReferredDistributors < 11)
									{
										$percentage = 10; 
									}
									else if ($transaction->ReferredDistributors < 21)
									{
										$percentage = 15; 
									}
									else if ($transaction->ReferredDistributors < 31)
									{
										$percentage = 20; 
									}
									else
									{
										$percentage = 30; 
									}
								}
							?>
							<th scope="col"><p class = "text-info text-center">{{$percentage > 0 ? $percentage.'%' : ''}}</p></th>
							<th scope="col" class = "text-right">{{$percentage > 0 ?  '$ '.number_format($percentage * $transaction->OrderTotal / 100, 2) : ''}}</th>
							<th scope="col" class = "text-center"><button type="button" class="btn btn-primary invoice" data-toggle="modal" data-target="#itemsModal" id = '{{$transaction->Invoice}}'>Items</button></th>
						</tr>
						<?php $page_total += $transaction->OrderTotal; ?>
						<?php endforeach; ?>
						<tr>
							<th scope="col"></th>
							<th scope="col"></th>
							<th scope="col"></th>
							<th scope="col"></th>
							<th scope="col"><p class = "text-secondary text-right">Total</p></th>
							<th scope="col"><p class = "text-primary text-right">$ {{number_format($page_total, 2)}}</p></th>
							<th scope="col"><p class = "text-secondary text-right">Commissions</p></th>
							<th scope="col"><p class = "text-success text-right">$ {{number_format($commissions, 2)}}</p></th>
							<th scope="col"></th>
						</tr>
					</tbody>
				</table>
				</div>
            </div>
            <hr class = "border border-primary"/>
			<form method = "post" action = "/commissions/transactions#report">
			@csrf
			<div class = "row">
				<div class = "col-md-2">
					<button type = "submit" class = <?php echo ($offset <= 0 ? "'btn btn-secondary btn-block'" : "'btn btn-primary btn-block'") ?>  name = "prevButton" id = "prevButton" value = "prevButton" <?php echo ($offset <= 0 ? 'disabled' : '') ?>>Previous</button>
				</div>
				<div class = "col-md-2">
					<button type = "submit" class = <?php echo ($next >= $total ? "'btn btn-secondary btn-block'" : "'btn btn-primary btn-block'") ?>  name = "nextButton" id = "prevButton" value = "nextButton" <?php echo ($next >= $total ? 'disabled' : '') ?>>Next</button>
				</div>
			</div>
			<input type="hidden" name="distributor" id="distributor" value="{{$distributor}}">
			<input type="hidden" name="startDate" id="startDate" value="{{$startDate}}">
			<input type="hidden" name="endDate" id="endDate" value="{{$endDate}}">
			<input type="hidden" name="prev" id="prev" value="{{$prev}}">
			<input type="hidden" name="next" id="next" value="{{$next}}">
			<input type="hidden" name="_token" value="{{csrf_token()}}">
			</form>
			<?php endif; ?>
			<br/>
        </div>
    </div>
    <div class="modal fade" id="itemsModal" tabindex="-1" role="dialog" aria-labelledby="itemsModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class = "text-center">
						<div class="spinner-border text-primary" role="status"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<script>
	$(document).ready(function(){
		
		var spinner = "<div class = 'text-center'><div class='spinner-border text-primary' role='status'></div></div>";
		
		// selecting dates
		$('.datepicker').datepicker({
			format: 'yyyy-mm-dd',
			startDate: '1900-01-01'
		});
		
		// autocomplete
		$('input.typeahead').typeahead({
			
			source: function (query, process) {
			
				return $.get('/commissions/autocomplete', { q: query }, function (data) {
				data = $.parseJSON(data);
				
				return process(data);
				});
			},
			showHintOnFocus:'all',
			minLength: 3,
			items: 100
		});
		
		$('.invoice').click(function(){
			
			var invoice = $(this).attr('id');
			
			$('.modal-body').html(spinner);

			// create request
			$.ajax({
			url: '/commissions/invoice/' + invoice,
			type: 'get',
			data: {},
			success: function(response){ 
				
				$('.modal-title').html("<b>Invoice: <span class = 'text-primary'>" + invoice + "</span></b>");
				
				// add response in modal body
				var html_response = "<table class = 'table table-striped table-bordered'>\n<thead class = 'thead-dark'><tr><th scope='col'>SKU</th><th scope='col'>Product Name</th><th scope='col'>Price</th><th scope='col'>Quantity</th><th scope='col'>Total</th></tr></thead><tbody>";
				
				var json = JSON.parse(response);
				
				var total = 0;
				
				json.forEach(item => {
					
					html_response += "<tr><th scope = 'col'>" + item['SKU'] + "</th>";
					
					html_response += "<th scope = 'col'>" + item['Product'] + "</th>";
					
					html_response += "<th scope = 'col' class = 'text-right'>$ " + item['Price'].toFixed(2) + "</th>";
					
					html_response += "<th scope = 'col' class = 'text-center'>" + item['Quantity'] + "</th>";
					
					html_response += "<th scope = 'col' class = 'text-right'>$ " + item['OrderTotal'].toFixed(2) + "</th></tr>";
					
					total += item['OrderTotal'];
				});
				
				if (json.length > 1) {
					html_response += "<tr><th scope = 'col'></th><th scope = 'col'></th><th scope = 'col'></th><th scope = 'col'><b><p class = 'text-secondary text-right'>Total</p></b></th><th scope = 'col'><p class = 'text-success text-right'><b>$ " + total.toFixed(2) + "</p></b></th></tr>";
				}
				
				html_response += "</tbody></table>";

				$('.modal-body').html(html_response);

				// display modal
				$('#itemsModal').modal('show');
			}});
		});
	});
	</script>
</body>
</html>
