<?php
/**
 * Created by PhpStorm.
 * User: michalploneczka
 * Date: 24.03.2014
 * Time: 20:16
 */

class Basket
{
	private $products = array();
	private $client = array();
	private $summaryPrice = 0 ;
	private $summaryQuantity = 0 ;
	private static $instance = null;
	private $session;
	private $freeShippingFrom = 99;
	private $paymentType;
	private $deliveryType;

	private $premiumCard = null;
	private $rebateCode  = null;

	public $contactFirstName;
	public $contactLastName;
	public $contactPhone;
	public $contactEmail;

	public $deliveryFirstName;
	public $deliveryLastName;
	public $deliveryPhone;
	public $deliveryStreet;
	public $deliveryHouse;
	public $deliveryFlat;
	public $deliveryPostCode;
	public $deliveryCity;
	public $deliveryCountry;

	public $invoiceFirstName;
	public $invoiceLastName;
	public $invoicePhone;
	public $invoiceStreet;
	public $invoiceHouse;
	public $invoiceFlat;
	public $invoicePostCode;
	public $invoiceCity;
	public $invoiceCountry;

	public $description;

	public $permission1;
	public $permission2;
	public $permission3;

	public function getContactFirstName()
	{
		return $this->contactFirstName;
	}

	public function getContactLastName()
	{
		return $this->contactLastName;
	}

	public function getContactPhone()
	{
		return $this->contactPhone;
	}

	public function getContactEmail()
	{
		return $this->contactEmail;
	}

	public function getDeliveryFirstName()
	{
		return $this->deliveryFirstName;
	}

	public function getDeliveryLastName()
	{
		return $this->deliveryLastName;
	}

	public function getDeliveryPhone()
	{
		return $this->deliveryPhone;
	}

	public function getDeliveryStreet()
	{
		return $this->deliveryStreet;
	}

	public function getDeliveryHouse()
	{
		return $this->deliveryHouse;
	}

	public function getDeliveryFlat()
	{
		return $this->deliveryFlat;
	}

	public function getDeliveryPostCode()
	{
		return $this->deliveryPostCode;
	}

	public function getDeliveryCity()
	{
		return $this->deliveryCity;
	}

	public function getDeliveryCountry()
	{
		return $this->deliveryCountry;
	}

	public function getInvoiceFirstName()
	{
		return $this->invoiceFirstName;
	}

	public function getInvoiceLastName()
	{
		return $this->invoiceLastName;
	}

	public function getInvoicePhone()
	{
		return $this->invoicePhone;
	}

	public function getInvoiceStreet()
	{
		return $this->invoiceStreet;
	}

	public function getInvoiceHouse()
	{
		return $this->invoiceHouse;
	}

	public function getInvoiceFlat()
	{
		return $this->invoiceFlat;
	}

	public function getInvoicePostCode()
	{
		return $this->invoicePostCode;
	}

	public function getInvoiceCity()
	{
		return $this->invoiceCity;
	}

	public function getInvoiceCountry()
	{
		return $this->invoiceCountry;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getPermission1()
	{
		return $this->permission1;
	}

	public function getPermission2()
	{
		return $this->permission2;
	}

	public function getPermission3()
	{
		return $this->permission3;
	}

	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new Basket();
		}

		return self::$instance;
	}



	public function add($productId, $productSize = 1, $quantity = 1, $friendlyName = null)
	{
		$added = false;
		$isAvailable = $this->isAvailable($productId, $productSize, $added);


		if ($isAvailable) {
			foreach ($this->products as &$product) {
				if ($product['id'] == $productId && $product['size'] == $productSize) {
					$product['quantity'] += $quantity;
					$added = true;
					break;
				}
			}
		}

		if (!$added) {
			$this->products[] = array(
				'id'           => $productId,
				'quantity'     => $quantity,
				'size'         => $productSize,
				'friendlyName' => $friendlyName
			);
			$added = true;
		}

		$this->save();

		if ($added) {
			$this->recalculateBasketParams();
		}

		return $added;
	}

	public function save()
	{
		$this->session['basket'] = array(
			'products'        => $this->products,
			'deliveryType'    => $this->deliveryType,
			'deliveryCost'    => null,
			'paymentType'     => $this->paymentType,
			'paymentCost'     => null,
			'summaryPrice'    => $this->summaryPrice,
			'summaryQuantity' => $this->summaryQuantity,
			'rebateCode'      => $this->rebateCode,
			'contactFirstName' => $this->contactFirstName,
			'contactLastName'  => $this->contactLastName,
			'contactPhone'     => $this->contactPhone,
			'contactEmail'     => $this->contactEmail,

			'deliveryFirstName' => $this->deliveryFirstName,
			'deliveryLastName'  => $this->deliveryLastName,
			'deliveryPhone'     => $this->deliveryPhone,
			'deliveryStreet'    => $this->deliveryStreet,
			'deliveryHouse'     => $this->deliveryHouse,
			'deliveryFlat'      => $this->deliveryFlat,
			'deliveryPostCode'  => $this->deliveryPostCode,
			'deliveryCity'      => $this->deliveryCity,
			'deliveryCountry'   => $this->deliveryCountry,

			'invoiceFirstName' => $this->invoiceFirstName,
			'invoiceLastName'  => $this->invoiceLastName,
			'invoicePhone'     => $this->invoicePhone,
			'invoiceStreet'    => $this->invoiceStreet,
			'invoiceHouse'     => $this->invoiceHouse,
			'invoiceFlat'      => $this->invoiceFlat,
			'invoicePostCode'  => $this->invoicePostCode,
			'invoiceCity'      => $this->invoiceCity,
			'invoiceCountry'   => $this->invoiceCountry,

			'description'      => $this->description,

			'permission1' => $this->permission1,
			'permission2' => $this->permission2,
			'permission3' => $this->permission3,

		);

		$this->session['code'] = $this->rebateCode;
	}

	public function remove($productId, $productSize = 1, $quantity = 1)
	{
		$removed = false;

		foreach ($this->products as &$product) {
			if ($product['id'] == $productId && $product['size'] == $productSize) {
				$product['quantity'] = 0;
				$removed = true;
				break;
			}
		}

		if ($removed) {
			$this->recalculateBasketParams();
		}

		$this->save();
	}

	public function changeQuantity($productId, $productSize = 1, $quantity = 1)
	{
		$removed = false;

		if ($quantity<0) {
			$quantity = 0;
		}

		foreach ($this->products as &$product) {
			if ($product['id'] == $productId && $product['size'] == $productSize) {
				$product['quantity'] = $quantity;
				$removed = true;
				break;
			}
		}

		if ($removed) {
			$this->recalculateBasketParams();
		}

		$this->save();
	}

	public function recalculateBasketParams()
	{
		$this->summaryQuantity = 0;
		$this->summaryPrice = 0;
		foreach ($this->products as $product) {
			$productObj = $this->getProduct($product['friendlyName']);
			$this->summaryQuantity += $product['quantity'];
			$this->summaryPrice += $productObj->getPrice()*$product['quantity'];
		}

		$this->save();
	}

	public function setRebateCode($code)
	{
		$this->rebateCode = $code;
		$this->save();
	}

	public function isAvailable($productId, $size = 1, $quantity=1)
	{
		$productId = (int)$productId;
		$quantity = (int)$quantity;

		$productDb = Yii::app()->db->createCommand("select hermes_index from products where id=".$productId)->queryAll();
		$index = $productDb[0]['hermes_index'];

		if ($size!=1) {
			$stock = Yii::app()->db->createCommand("select sum(ilosc) as ilosc from hermes_magazyn where indeks='".$index."' and size='".$size."'")->queryAll();
		} else {
			$stock = Yii::app()->db->createCommand("select sum(ilosc) as ilosc from hermes_magazyn where indeks='".$index."'")->queryAll();
		}

		return ($stock[0]['ilosc']>=$quantity)?true:false;
	}

	public function getStock($productId, $size = 1)
	{
		$productId = (int)$productId;

		$productDb = Yii::app()->db->createCommand("select hermes_index from products where id=".$productId)->queryAll();
		$index = $productDb[0]['hermes_index'];

		if ($size!=1) {
			$stock = Yii::app()->db->createCommand("select sum(ilosc) as ilosc from hermes_magazyn where indeks='".$index."' and size='".$size."'")->queryAll();
		} else {
			$stock = Yii::app()->db->createCommand("select sum(ilosc) as ilosc from hermes_magazyn where indeks='".$index."'")->queryAll();
		}

		return (int)$stock[0]['ilosc'];
	}

	public function getDeliveryPrice()
	{
		$deliveryPrice = 0;

		if ($this->session['basket']['deliveryType']==1 && !$this->isFreeShipping()) {
			if ($this->session['basket']['paymentType']==1) {
				$deliveryPrice += 5;
			}
			$deliveryPrice += 14;
		} elseif ($this->session['basket']['deliveryType']==2 && !$this->isFreeShipping()) {
			if ($this->session['basket']['paymentType']==1) {
				$deliveryPrice += 5;
			}
			$deliveryPrice += 14;
		}

		return $deliveryPrice;
	}

	public function setDeliveryType($type)
	{
		$this->deliveryType = (int)$type;
		$this->save();
	}

	public function setPaymentType($type)
	{
		$this->paymentType = (int)$type;
		$this->save();
	}

	public function getProduct($friendlyName, $lang = 'PL_pl')
	{
		$model = new Product();
		$product = $model->find($friendlyName);

		return $product;
	}

	public function getProducts()
	{
		$items = array();
		$i = 0;
		foreach ($this->products as $product) {
			if ($product['quantity']>0) {
				$items[$i] = $this->getProduct($product['friendlyName']);
				$items[$i]->basketQuantity = $product['quantity'];
				$items[$i]->basketSize = $product['size'];
				$i++;
			}
		}

		return $items;
	}

	public function getTotalPrice()
	{
		return $this->summaryPrice;
	}

	public function isFreeShipping()
	{
		return ($this->summaryPrice>=$this->freeShippingFrom)?true:false;
	}

	public function priceToFreeShipping()
	{
		return $this->freeShippingFrom - $this->summaryPrice;
	}

	public function getFreeShipingFromPrice()
	{
		return $this->freeShippingFrom;
	}

	public function getFinalPrice()
	{
		$rebate = 0;
		$rModel = new Rebate();
		if ($this->rebateCode!==0) {
			$rebate = $rModel->findByAttributes(array(
					'code' => $this->rebateCode
				));
		}

		$brands = array();
		$basketBrands = array();
		$categories = array();
		$basketCategories = array();

		$hasBrand = false;
		$hasCategory = false;

		foreach ($this->products as $product) {
			$pModel = new Product();
			$pObject = $pModel->find($product['friendlyName']);
			$basketCategories[] = $pObject->getMainCategoryId();
			$basketBrands[] = $pObject->getBrandId();
		}

		if ($rebate!==0 && is_object($rebate) && $rebate->id) {
			foreach ($rebate->possibleBrands as $brand) {
				$brands[] = $brand->id_brand;
			}

			foreach ($rebate->possibleCategories as $brand) {
				$categories[] = $brand->id_category;
			}
		}

		foreach ($brands as $item) {
			if (in_array($item, $basketBrands)) {
				$hasBrand = true;
				break;
			}
		}

		if (count($brands)==0) {
			$hasBrand = true;
		}

		foreach ($categories as $item) {
			if (in_array($item, $basketCategories)) {
				$hasCategory = true;
				break;
			}
		}

		if (count($categories)==0) {
			$hasCategory = true;
		}

		$summary = $this->summaryPrice;

		if ($rebate && $rebate->getFromPrice()<=$this->getTotalPrice() && $hasBrand && $hasCategory) {
			if ($rebate->getType()=='percent') {
				$summary -= ($rebate->getValue()*$summary/100);
			} else {
				$summary -= $rebate->getValue();
			}
		}

		if ($summary<0) {
			$summary = 0;
		}



		if (count($this->products)) {
			return $summary+$this->getDeliveryPrice();
		} else {
			return 0;
		}
	}

	public function getProductsCount()
	{
		return $this->summaryQuantity;
	}

	public function getDeliveryTypeName()
	{
		$name = '';
		if ($this->deliveryType==1) {
			$name = 'Poczta Polska';
		} elseif ($this->deliveryType==2) {
			$name = 'Przesyłka kurierska';
		} else {
			$name = 'Odbiór osobisty w sklepie';
		}

		return $name;
	}

	public function getDeliveryType()
	{
		return $this->deliveryType;
	}

	public function getPaymentTypeName()
	{
		$name = '';
		if ($this->paymentType==1) {
			$name = 'Płatność przy odbiorze';
		} elseif ($this->paymentType==2) {
			$name = 'Przelew bankowy';
		} else {
			$name = 'Przelew internetowy';
		}

		return $name;
	}

	public function getPaymentType()
	{
		return $this->paymentType;
	}

	public function getInvoiceCompany()
	{

	}

	public function getInvoiceNIP()
	{

	}

	public function createNewOrder()
	{
		$token = md5(microtime() . 'adding');
		$order = new Order();
		$order->buyer_city = $this->deliveryCity;
		$order->buyer_postcode = $this->deliveryPostCode;

		$address = $this->deliveryStreet.' '.$this->deliveryHouse;

		if (!empty($this->deliveryFlat)) {
			$address .= '/'.$this->deliveryFlat;
		}
		$address .= ' '.$order->buyer_postcode.' '.$order->buyer_city;

		$order->buyer_address = $address;

		$order->buyer_country = $this->deliveryCountry;
		$order->buyer_house = $this->deliveryHouse;
		$order->buyer_flat = $this->deliveryFlat;
		$order->hash = $token;

		$order->order_date = date('Y-m-d H:i:s');
		$order->modify_date = date('Y-m-d H:i:s');
		$order->webservice_status = 0;
		$order->total = $this->getFinalPrice();
		$order->buyer_name = $this->contactFirstName.' '.$this->contactLastName;
		$order->buyer_lastname = $this->contactLastName;
		$order->buyer_email = $this->contactEmail;
		$order->buyer_phone = $this->contactPhone;
		$order->description = $this->description;
		$order->delivery_lastname = $this->deliveryLastName;
		$order->delivery_name = $this->deliveryFirstName;
		$order->shipping = $this->deliveryType;
		$order->payment = $this->paymentType;
		$order->sklep_internetowy_id = 1;
		$order->save();

		foreach ($this->products as $product) {
			for ($i=0; $i<$product['quantity']; $i++) {
				$op = new OrderProduct();
				$op->order_id = $order->id;
				$op->product_id = $product['id'];
				$op->size = $product['size'];

				$productDb = Yii::app()->db->createCommand()
					->select('products.hermes_index, products_shop.price, products_shop.old_price')
					->from('products')
					->join('products_shop', 'products.id=products_shop.product_id')
					->andWhere('deleted = 0 and hidden=0 and sklep_internetowy_id=1 and (quantity>0 or delivery=true)')
					->andWhere("products.friendly_name='".$product['friendlyName']."'")
					->queryRow();

				$op->hermes_index = $productDb['hermes_index'];
				$op->price = $productDb['price'];
				$op->creation_date = date('Y-m-d H:i:s');
				$op->modify_date = date('Y-m-d H:i:s');

				$op->save();
			}

		}

		$message = Yii::app()->db->createCommand()
			->select('*')
			->from('mailing')
			->andWhere('id=10')
			->queryRow();


		$link = 'http://www.example.com/zamowienie/szczegoly/' . $order->id . '/' . $token;
		$message['content'] = str_replace('{{LINK}}', $link, $message['content']);

		$deliveryAddress = $order->buyer_postcode.' '.$order->buyer_city.', '.$order->buyer_address.' '.$order->buyer_house;

		if (!empty($order->buyer_flat)) {
			$deliveryAddress .= '/'.$order->buyer_flat;
		}

		$buyerName = $order->buyer_name.' '.$order->buyer_lastname;

		$message['content'] = str_replace('{{ADRES}}', $deliveryAddress, $message['content']);
		$message['content'] = str_replace('{{SUMA}}', ($order->total+19), $message['content']);
		$message['content'] = str_replace('{{PUNKTY}}', 0, $message['content']);
		$message['content'] = str_replace('{{ID}}', $order->id, $message['content']);
		$message['content'] = str_replace('{{USER}}', $buyerName, $message['content']);
		$message['content'] = str_replace('{{KWOTA}}', $order->total, $message['content']);
		$message['content'] = str_replace('{{PRZESYLKA}}', 19, $message['content']);

		$message['header'] = str_replace('{{ID}}', $order->id, $message['header']);

		if($this->deliveryType==1) {
			$naddress = 'Poczta Polska';
		} elseif ($this->deliveryType==2) {
			$naddress = 'Przesylka kurierska';
		} elseif ($this->deliveryType==6) {
			$naddress = 'Darmowa przesylka kurierska';
		} else {
			$naddress = 'Odbiór osobisty w sklepie';
		}

		$message['content'] = str_replace('{{FORMA_PRZESYLKI}}', $naddress, $message['content']);

		if($this->paymentType == 2) {
			$bankowiec = 'Numer konta bankowego: 78 1140 2017 0000 4202 0801 9012';
			$message['content'] = str_replace('{{KONTO}}', $bankowiec, $message['content']);
			$message['content'] = str_replace('{{PRZELEW}}', 'Dane do przelewu: DANSPORT 44-117 Gliwice, ul. Oriona 36', $message['content']);
		} else {
			$message['content'] = str_replace('{{KONTO}}', '', $message['content']);
			$message['content'] = str_replace('{{PRZELEW}}', '', $message['content']);
		}

		$mail = new YiiMailMessage;
		$mail->setBody($message['content'], 'text/html');
		$mail->setSubject($message['header']);
		$mail->addTo($order->buyer_email);
		$mail->from = 'zamowienia@example.com';
		Yii::app()->mail->transportType = 'smtp';

		Yii::app()->mail->transportOptions = array(
			'host' => 'mail.sklepbiegacza.pl',
			'username' => 'zamowienia@example.com',
			'password' => 'hod9cu7Uasdsad',
			'port'     => '25'
		);
		Yii::app()->mail->send($mail);

		$this->products = array();
		$this->recalculateBasketParams();
		$this->save();

		return $order->id;
	}

	private function __construct()
	{
		$this->session = Yii::app()->session;


		if (isset($this->session['basket']) && isset($this->session['basket']['products'])) {
			$this->products = $this->session['basket']['products'];
			$this->summaryPrice = $this->session['basket']['summaryPrice'];
			$this->summaryQuantity = $this->session['basket']['summaryQuantity'];
			$this->deliveryType = $this->session['basket']['deliveryType'];
			$this->paymentType = $this->session['basket']['paymentType'];

			$this->contactFirstName = $this->session['basket']['contactFirstName'];
			$this->contactLastName = $this->session['basket']['contactLastName'];
			$this->contactPhone = $this->session['basket']['contactPhone'];
			$this->contactEmail = $this->session['basket']['contactEmail'];

			$this->deliveryFirstName = $this->session['basket']['deliveryFirstName'];
			$this->deliveryLastName = $this->session['basket']['deliveryLastName'];
			$this->deliveryPhone = $this->session['basket']['deliveryPhone'];
			$this->deliveryStreet = $this->session['basket']['deliveryStreet'];
			$this->deliveryHouse = $this->session['basket']['deliveryHouse'];
			$this->deliveryFlat = $this->session['basket']['deliveryFlat'];
			$this->deliveryPostCode = $this->session['basket']['deliveryPostCode'];
			$this->deliveryCity = $this->session['basket']['deliveryCity'];
			$this->deliveryCountry = $this->session['basket']['deliveryCountry'];

			$this->invoiceFirstName = $this->session['basket']['invoiceFirstName'];
			$this->invoiceLastName = $this->session['basket']['invoiceLastName'];
			$this->invoicePhone = $this->session['basket']['invoicePhone'];
			$this->invoiceStreet = $this->session['basket']['invoiceStreet'];
			$this->invoiceHouse = $this->session['basket']['invoiceHouse'];
			$this->invoiceFlat = $this->session['basket']['invoiceFlat'];
			$this->invoicePostCode = $this->session['basket']['invoicePostCode'];
			$this->invoiceCity = $this->session['basket']['invoiceCity'];
			$this->invoiceCountry = $this->session['basket']['invoiceCountry'];

			$this->description = $this->session['basket']['description'];

			$this->permission1 = $this->session['basket']['permission1'];
			$this->permission2 = $this->session['basket']['permission2'];
			$this->permission3 = $this->session['basket']['permission3'];

			$this->rebateCode = $this->session['code'];
		} else {
			$this->products = array();
			$this->session['basket'] = array(
				'products'        => array(),
				'deliveryType'    => 1,
				'deliveryCost'    => 19,
				'paymentType'     => 1,
				'paymentCost'     => null,
				'summaryPrice'    => 0,
				'summaryQuantity' => 0,
				'rebateCode'      => null,
				'contactFirstName' => null,
				'contactLastName'  => null,
				'contactPhone'     => null,
				'contactEmail'     => null,

				'deliveryFirstName' => null,
				'deliveryLastName'  => null,
				'deliveryPhone'     => null,
				'deliveryStreet'    => null,
				'deliveryHouse'     => null,
				'deliveryFlat'      => null,
				'deliveryPostCode'  => null,
				'deliveryCity'      => null,
				'deliveryCountry'   => null,

				'invoiceFirstName' => null,
				'invoiceLastName'  => null,
				'invoicePhone'     => null,
				'invoiceStreet'    => null,
				'invoiceHouse'     => null,
				'invoiceFlat'      => null,
				'invoicePostCode'  => null,
				'invoiceCity'      => null,
				'invoiceCountry'   => null,

				'description'      => null,

				'permission1' => false,
				'permission2' => false,
				'permission3' => false,
			);

			$this->session['code'] = 0;
		}

	}

	private function __clone() {}
}