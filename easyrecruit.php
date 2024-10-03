<?php
declare(strict_types=1);

/**
 * Получить информацию о заказе по его идентификатору.
 * 
 * @param int $order_id Идентификатор заказа
 * @return array Информация о заказе
 * @throws Exception В случае ошибки SQL
 */
function getOrderDetails(int $order_id): array
{
	/*
	 * В реальном проекте подключение к базе нужно вынести, запрос строить с помощью QueryBuilder.
	 */
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=easyrecruit;charset=utf8mb4', 'root', 'root');
	$stmt = $pdo->prepare(<<<SQL
SELECT 
    p.id, 
    p.name, 
    p.price, 
    p.discount, 
    op.amount 
FROM orders_products AS op 
    JOIN products AS p ON p.id = op.product_id
WHERE 
    op.order_id = ?
SQL);

	if (!$stmt->execute([$order_id])) {
		throw new Exception('Unable execute SQL query.');
	}
	
	$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$totalPrice = 0;
	$finalPrice = 0;
	
	foreach ($products as &$product) {
		[
			'price' => $price,
			'discount' => $discount,
			'amount' => $amount
		] = $product;
		
		$totalPrice += $price * $amount;
		$finalPrice += $price * $amount * (1 - $discount);
	}
	
	return [
		'products' => $products,
		'summary' => [
			'total_price' => $totalPrice,
			'final_price' => $finalPrice,
			'saved' => $totalPrice - $finalPrice
		]
	];
}

try {
    echo json_encode(getOrderDetails(1), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $exception) {
    echo 'Не удалось получить информацию о заказе.';
}