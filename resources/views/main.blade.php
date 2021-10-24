<!DOCTYPE html>
<html lang="{{str_replace('_', '-', app()->getLocale())}}">

@include('header', ['page_title' => "{$page_title}"]);

<body>
	<div class="containter">
		<div class="container">
			<div class="row">
				<div class="col-sm-6">
					<h2 class="text-primary"><b>{{$page_title}}</b></h2>
				</div>
			</div>
			<hr class="border border-primary" />
			<div class="row">
				<div class="col-sm-6">
					<div class="card border-info">
						<div class="card-body">
							<h5 class="card-title text-info"><b>Transactions Report</b></h5>
							<p class="card-text">Lists all transactions, purchases, referrals, and estimates the referrer's commission on each order.</p>
							<a href="/commissions/report" class="btn btn-primary">View Report</a>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="card border-info">
						<div class="card-body">
							<h5 class="card-title text-success"><b>Top 100 Distributors</b></h5>
							<p class="card-text">Ranks the distributors based on sales generated, whether purchased by a customer or another distributor.</p>
							<a href="/commissions/top100" class="btn btn-primary">See Rankings</a>
						</div>
					</div>
				</div>
			</div>
			<hr class="border border-primary" />
		</div>
	</div>
</body>

</html>