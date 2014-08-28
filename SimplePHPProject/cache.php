<?php
/**
 * Created by PhpStorm.
 * User: michalploneczka
 * Date: 28.08.2014
 * Time: 13:19
 */

class Cache implements Backend
{
	private static $instance = null;
	private $cache = null;

	public static function getInstance()
	{
		if (self::$instance===null) {
			self::$instance = new Cache();
		}

		return self::$instance;
	}

	public function get($store, $sku)
	{
		$this->connect();

		$num = date('d')%2;
		$value['stock_qte'] = $this->cache->get(md5($store.$sku.'stock'.$num));
		$value['sale_qte'] = $this->cache->get(md5($store.$sku.'sale'.$num));

		return $value;
	}

	public function set($store, $sku, $stock, $sale)
	{
		$num = date('d')%2;
		$this->cache->set(md5($store.$sku.'stock'.$num), $stock, Array('nx', 'ex'=>REDIS_TTL));
		$this->cache->set(md5($store.$sku.'sale'.$num), $sale, Array('nx', 'ex'=>REDIS_TTL));
	}

	private function connect()
	{
		if (is_null($this->cache)) {
			try {
				$this->cache = new Redis();
				$this->cache->connect(REDIS_HOST);
			} catch (Exception $e) {
				error_log($e);
			}
		}
	}

	private function __construct()
	{

	}

	private function __clone()
	{

	}
}