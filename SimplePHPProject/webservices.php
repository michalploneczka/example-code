<?php
/**
 * Created by PhpStorm.
 * User: michalploneczka
 * Date: 28.08.2014
 * Time: 13:19
 */

final class Webservices implements Backend
{
	private static $instance = null;

	public static function getInstance()
	{
		if (self::$instance===null) {
			self::$instance = new Webservices();
		}

		return self::$instance;
	}

	public function get($store, $sku)
	{
		$data = array();
		foreach ($store as $item) {
			foreach ($sku as $product) {
				$set = array(
					'store' => $item,
					'sku'   => $product
				);

				$value = Cache::getInstance()->get($item, $product);

				if (!$value['sale_qte']) {
					$value = Database::getInstance()->get($item, $product);

					if ($value) {
						Cache::getInstance()->set($item, $product, $value['stock_qte'], $value['sale_qte']);
					}
				}


				$set['stock_qte'] = $value['stock_qte'];
				$set['sale_qte'] = $value['sale_qte'];

				$data[] = $set;
			}
		}

		return $data;
	}

	private function __construct()
	{

	}

	private function __clone()
	{

	}
}