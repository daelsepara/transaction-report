<!DOCTYPE html>
<html lang="{{str_replace('_', '-', app()->getLocale())}}">

@include('header', ['page_title' => "{$page_title}"])

<body>
	<div class="containter">
		<div class="container">
			<br />
			<div class="row">
				<div class="col-auto">
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button" id="tools-menu" data-toggle="dropdown" data-bs-auto-close="true">
							Tools
						</button>
						<ul class="dropdown-menu" role="menu" aria-labelledby="tools-menu" bg-light>
							<li class="dropdown-header">Available Tools</li>
							<li><a class="dropdown-item" href="/commissions/transactions">Transaction Report</a></li>
							<li>
								<hr class="dropdown-divider" />
							</li>
							<li class="dropdown-header">Go back to the main page</li>
							<li><a class="dropdown-item" href="/commissions">Back</a></li>
						</ul>
					</div>
				</div>
			</div>
			<hr class="border border-primary" />
			<div class="row">
				<div class="col-auto">
					<h1 class="text-primary">{{$page_title}}</h1>
				</div>
			</div>
			<hr class="border border-primary" />
			<br />
			<a name='rankings' />
			<?php if (count($distributors) > 0) : ?>
				<div class="row">
					<div class="col-auto">
						<table class="table table-striped table-bordered">
							<thead class="thead-dark">
								<tr>
									<th scope="col">Top</th>
									<th scope="col">Distributor Name</th>
									<th scope="col">Total Sales</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($distributors as $distributor) : ?>
									<tr>
										<th scope="col">{{$distributor->Top}}</th>
										<th scope="col">
											<p class="text-primary">{{$distributor->Distributor}}</p>
										</th>
										<th scope="col" class="text-right">
											<p class="text-success">$ {{number_format($distributor->OrderTotal, 2)}}</p>
										</th>
										<th scope="col" class="text-center"><button type="button" class="btn btn-primary sales" data-toggle="modal" data-target="#itemsModal" data-distributor-id='{{$distributor->ID}}' data-distributor='{{$distributor->Distributor}}'>Sales</button></th>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<hr class="border border-primary" />
				<form method="post" action "/commissions/top100#rankings">
					@csrf
					<div class="row">
						<div class="col-md-2">
							<button type="submit" class=<?php echo ($offset <= 0 ? "'btn btn-secondary btn-block'" : "'btn btn-primary btn-block'") ?> name="prevButton" id="prevButton" value="prevButton" <?php echo ($offset <= 0 ? 'disabled' : '') ?>>Previous</button>
						</div>
						<div class="col-md-2">
							<button type="submit" class=<?php echo ($next >= $total ? "'btn btn-secondary btn-block'" : "'btn btn-primary btn-block'") ?> name="nextButton" id="prevButton" value="nextButton" <?php echo ($next >= $total ? 'disabled' : '') ?>>Next</button>
						</div>
					</div>
					<input type="hidden" name="prev" id="prev" value="{{$prev}}">
					<input type="hidden" name="next" id="next" value="{{$next}}">
					<input type="hidden" name="_token" value="{{csrf_token()}}">
				</form>
			<?php endif; ?>
			<br />
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
					<span class="text-center">
						<div class="spinner-border text-primary" role="status"></div>
					</span>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<script>
		$(document).ready(function() {

			var spinner = "<div class = 'text-center'><div class='spinner-border text-primary' role='status'></div></div>";

			$('.sales').click(function() {

				var distributor = $(this).attr('data-distributor-id');
				var name = $(this).attr('data-distributor');

				$('.modal-body').html(spinner);

				// create request
				$.ajax({
					url: '/commissions/sales/' + distributor,
					type: 'get',
					data: {},
					success: function(response) {

						$('.modal-title').html("<b>Distributor: <span class = 'text-primary'>" + name + "</span></b>");

						// add response in modal body
						var html_response = "<table class = 'table table-striped table-bordered'>\n<thead class = 'thead-dark'><tr><th scope='col'>Invoice</th><th scope='col'>SKU</th><th scope='col'>Product</th><th scope='col'>Quanity</th><th scope='col'>Price</th><th scope='col'>Total</th></tr></thead><tbody>";

						var json = JSON.parse(response);

						var total = 0;

						json.forEach(item => {

							html_response += "<tr><th scope = 'col'>" + item['Invoice'] + "</th>";

							html_response += "<th scope = 'col'>" + item['SKU'] + "</th>";

							html_response += "<th scope = 'col'>" + item['Product'] + "</th>";

							html_response += "<th scope = 'col' class = 'text-center'>" + item['Quantity'] + "</th>";

							html_response += "<th scope = 'col' class = 'text-right'>$ " + item['Price'].toFixed(2) + "</th>";

							html_response += "<th scope = 'col' class = 'text-right'>$ " + item['OrderTotal'].toFixed(2) + "</th></tr>";

							total += item['OrderTotal'];
						});

						if (json.length > 1) {
							html_response += "<tr><th scope = 'col'></th><th scope = 'col'></th><th scope = 'col'></th><th scope = 'col'></th><th scope = 'col'><b><p class = 'text-secondary text-right'>Total</p></b></th><th scope = 'col'><p class = 'text-success text-right'>$ " + total.toFixed(2) + "</p></th></tr>";
						}

						html_response += "</tbody></table>";

						$('.modal-body').html(html_response);

						// display modal
						$('#itemsModal').modal('show');
					}
				});
			});
		});
	</script>
</body>

</html>