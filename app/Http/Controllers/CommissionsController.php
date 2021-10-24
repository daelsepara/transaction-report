<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CommissionsController extends Controller
{
	public function index()
	{
		$data = [];

		$other = [];

		$data['page_title'] = 'Commissions Tools';

		return view('main', $data, $other);
	}

	// view transactions
	public function report(Request $request)
	{
		$parameters = [];

		$data = [];

		if ($request->isMethod('post')) {
			// get all parameters
			$parameters = $request->all();
		}

		$limit = 5;

		$offset = 0;

		$other = [];

		$data['distributor'] = isset($parameters['distributor']) ? $parameters['distributor'] : '';
		$data['startDate'] = isset($parameters['startDate']) ? $parameters['startDate'] : '';
		$data['endDate'] = isset($parameters['endDate']) ? $parameters['endDate'] : '';

		$data['page_title'] = 'Transaction Report';

		$query = "SELECT orders.invoice_number Invoice, CONCAT(purchasers.first_name, ' ', purchasers.last_name) Purchaser, IF(user_category.category_id = 1, CONCAT(distributors.first_name, ' ', distributors.last_name), '') Distributor, IF(MAX(counts.ReferredDistributors) IS NULL, 0, MAX(counts.ReferredDistributors)) ReferredDistributors, orders.order_date OrderDate, SUM(order_items.qantity * products.price) OrderTotal FROM orders JOIN order_items ON orders.id = order_items.order_id JOIN products ON order_items.product_id = products.id JOIN users purchasers ON orders.purchaser_id = purchasers.id JOIN users distributors ON purchasers.referred_by = distributors.id JOIN user_category ON distributors.id = user_category.user_id LEFT JOIN (SELECT SUM(referrals.referred_id) OVER (partition by referrals.distributor_id order by referrals.enrolled_date) ReferredDistributors, referrals.distributor_id, referrals.enrolled_date FROM (SELECT distributors.id distributor_id, COUNT(DISTINCT referred.id) referred_id, referred.enrolled_date FROM users distributors JOIN user_category ON distributors.id = user_category.user_id JOIN users referred ON distributors.id = referred.referred_by JOIN user_category referred_category ON referred.id = referred_category.user_id WHERE user_category.category_id = 1 AND referred_category.category_id = 1 GROUP BY distributor_id, enrolled_date) referrals) counts ON distributors.id = counts.distributor_id";

		$where = "WHERE counts.enrolled_date <= orders.order_date";

		$substitute = [];

		if (strlen(trim($data['distributor'])) > 0) {
			$where .= " AND (distributors.id = ? OR distributors.username = ? OR distributors.first_name = ? OR distributors.last_name = ?)";

			$substitute[] = $data['distributor'];
			$substitute[] = $data['distributor'];
			$substitute[] = $data['distributor'];
			$substitute[] = $data['distributor'];
		}

		if (strlen(trim($data['startDate'])) > 0) {
			$where .= " AND orders.order_date >= ?";

			$substitute[] = $data['startDate'];
		}

		if (strlen(trim($data['endDate'])) > 0) {
			$where .= " AND orders.order_date <= ?";

			$substitute[] = $data['endDate'];
		}

		$group = "GROUP BY Invoice, Purchaser, Distributor, ReferredDistributors, OrderDate";

		$build_query = $query . " " . $where . " " . $group;

		$final_query = "SELECT final.Invoice, final.Purchaser, final.Distributor, MAX(final.ReferredDistributors) ReferredDistributors, final.OrderDate, final.OrderTotal FROM (" . $build_query . ") final GROUP BY final.Invoice, final.Purchaser, final.Distributor, final.OrderDate, final.OrderTotal";


		// Get all records to compute total commissions
		$total = DB::connection('mysql')->select($final_query, $substitute);

		$prev = 0;

		$next = $offset + $limit;

		if (isset($parameters['prevButton'])) {
			$offset = isset($parameters['prev']) ? $parameters['prev'] : 0;

			$next = $offset + $limit;
			$prev = $offset - $limit;

			if ($prev < 0) $prev = 0;
		}

		if (isset($parameters['nextButton'])) {
			if ($offset + $limit < count($total)) {
				$offset = isset($parameters['next']) ? $parameters['next'] : 10;

				$next = $offset + $limit;
				$prev = $offset - $limit;

				if ($prev < 0) $prev = 0;
			}
		}

		$order = "ORDER BY final.OrderDate, final.Invoice";

		$limits = "LIMIT ?, ?";

		$substitute[] = $offset;
		$substitute[] = $limit;

		$transactions = DB::connection('mysql')->select($final_query . " " . $order . " " . $limits, $substitute);

		$commissions = 0;

		foreach ($transactions as $transaction) {
			if (strlen(trim($transaction->Distributor)) > 0) {
				$percentage = 0;

				if ($transaction->ReferredDistributors < 5) {
					$percentage = 5;
				} else if ($transaction->ReferredDistributors < 11) {
					$percentage = 10;
				} else if ($transaction->ReferredDistributors < 21) {
					$percentage = 15;
				} else if ($transaction->ReferredDistributors < 31) {
					$percentage = 20;
				} else if ($transaction->ReferredDistributors > 30) {
					$percentage = 30;
				}

				$commissions += $percentage * $transaction->OrderTotal / 100;
			}
		}

		$data['transactions'] = $transactions;

		$data['prev'] = $prev;
		$data['next'] = $next;
		$data['total'] = count($total);
		$data['limit'] = $limit;
		$data['offset'] = $offset;
		$data['startDate'] = trim($data['startDate']);
		$data['endDate'] = trim($data['endDate']);
		$data['commissions'] = $commissions;

		return view('report', $data, $other);
	}

	// top 100 distributors based on sales
	public function top100(Request $request)
	{
		$parameters = [];
		$other = [];
		$data = [];

		if ($request->isMethod('post')) {
			// get all parameters
			$parameters = $request->all();
		}

		$query = "SELECT ID, Distributor, OrderTotal, RANK() OVER (ORDER BY OrderTotal DESC) AS Top FROM (SELECT distributors.id ID, CONCAT(distributors.first_name, ' ', distributors.last_name) Distributor, SUM(order_items.qantity * products.price) OrderTotal FROM orders JOIN order_items ON orders.id = order_items.order_id JOIN products ON order_items.product_id = products.id JOIN users purchasers ON orders.purchaser_id = purchasers.id JOIN users distributors ON purchasers.referred_by = distributors.id JOIN user_category ON distributors.id = user_category.user_id WHERE user_category.category_id = 1 GROUP BY ID, Distributor ORDER BY OrderTotal DESC LIMIT 100) Sales";

		$total = 100;

		$limit = 5;

		$offset = 0;

		$prev = 0;

		$next = $offset + $limit;

		if (isset($parameters['prevButton'])) {
			$offset = isset($parameters['prev']) ? $parameters['prev'] : 0;

			$next = $offset + $limit;

			$prev = $offset - $limit;

			if ($prev < 0) $prev = 0;
		}

		if (isset($parameters['nextButton'])) {
			if ($offset + $limit < $total) {
				$offset = isset($parameters['next']) ? $parameters['next'] : 10;

				$next = $offset + $limit;
				$prev = $offset - $limit;

				if ($prev < 0) $prev = 0;
			}
		}

		$limits = "LIMIT ?, ?";

		$substitute = [];
		$substitute[] = $offset;
		$substitute[] = $limit;

		$data['page_title'] = 'Top 100 Distributors';

		$data['distributors'] = DB::connection('mysql')->select($query . " " . $limits, $substitute);

		$data['prev'] = $prev;
		$data['next'] = $next;
		$data['total'] = $total;
		$data['limit'] = $limit;
		$data['offset'] = $offset;

		return view('top100', $data, $other);
	}

	// view all items in invoice (order)
	public function invoice(string $id)
	{
		$query = "SELECT orders.invoice_number Invoice, products.sku SKU, products.name Product, order_items.qantity Quantity, products.price Price, (order_items.qantity * products.price) OrderTotal FROM orders JOIN order_items ON orders.id = order_items.order_id JOIN products ON order_items.product_id = products.id WHERE orders.invoice_number = ?";

		$result = DB::connection('mysql')->select($query, [$id]);

		return json_encode($result);
	}

	// view all sales generated by distributor
	public function sales(string $id)
	{
		$query = "SELECT DISTINCT orders.invoice_number Invoice, products.sku SKU, products.name Product, SUM(order_items.qantity) Quantity, products.price Price, SUM(order_items.qantity * products.price) OrderTotal FROM orders JOIN order_items ON orders.id = order_items.order_id JOIN products ON order_items.product_id = products.id JOIN users purchaser ON orders.purchaser_id = purchaser.id JOIN users distributor ON purchaser.referred_by = distributor.id JOIN user_category ON distributor.id = user_category.user_id WHERE user_category.category_id = 1 AND distributor.id = ? GROUP BY Invoice, SKU, Product, Price";

		$result = DB::connection('mysql')->select($query, [$id]);

		return json_encode($result);
	}

	// for autocomplete
	public function autocomplete(Request $request)
	{
		$parameters = [];

		if ($request->isMethod('get')) {
			// get all parameters
			$parameters = $request->all();
		}

		$query = "SELECT distributors.id, distributors.first_name, distributors.last_name, distributors.username FROM users distributors JOIN user_category ON distributors.id = user_category.user_id WHERE user_category.category_id = 1";

		$result = [];

		if (isset($parameters['q']) && strlen(trim($parameters['q'])) > 0) {
			$substitute = [];

			$query .= " AND (distributors.id = ? OR distributors.username LIKE ? OR distributors.first_name LIKE ? OR distributors.last_name LIKE ?)";

			$substitute[] = $parameters['q'];
			$substitute[] = '%' . $parameters['q'] . '%';;
			$substitute[] = '%' . $parameters['q'] . '%';
			$substitute[] = '%' . $parameters['q'] . '%';

			$users = DB::connection('mysql')->select($query, $substitute);


			foreach ($users as $user) {
				$search = trim($parameters['q']);

				if (strpos($user->first_name, $search) !== false) {
					$result[] = $user->first_name;
				}

				if (strpos($user->last_name, $search) !== false) {
					$result[] = $user->last_name;
				}

				if (strpos($user->username, $search) !== false) {
					$result[] = $user->username;
				}

				if ($user->id == $search) {
					$result[] = "{$user->id}";
				}
			}
		}

		return json_encode($result);
	}
}
