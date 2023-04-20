<?
if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true ) {
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Sale\Compatible\DiscountCompatibility;
use \Bitrix\Sale\Basket;
use \Bitrix\Sale\Discount\Gift;
use \Bitrix\Sale\Fuser;

class iplogicSaleProductGifts extends \CBitrixComponent
	implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{

	public $order;
	protected $arLastOrder;

	/** @var ErrorCollection */
	protected $errorCollection;

	function __construct($component = null)
	{
		parent::__construct($component);

		$a = explode("/", $_SERVER['SCRIPT_NAME']);
		$a[count($a) - 1] = "";
		$_SERVER['SCRIPT_DIR_NAME'] = implode("/", $a);

		$this->errorCollection = new ErrorCollection();

		if( !Loader::includeModule('iblock') ) {
			$this->setError('No iblock module');
		};

		if( !Loader::includeModule('sale') ) {
			$this->setError('No sale module');
		};

		if( !Loader::includeModule('catalog') ) {
			$this->setError('No catalog module');
		};
	}

	public function configureActions()
	{
		//fill it, or use default
		return [];
	}

	public function onPrepareComponentParams($arParams)
	{
		if(
			isset($arParams['IS_AJAX'])
			&& ($arParams['IS_AJAX'] == 'Y' || $arParams['IS_AJAX'] == 'N')
		) {
			$arParams['IS_AJAX'] = $arParams['IS_AJAX'] == 'Y';
		}
		else {
			if(
				isset($this->request['is_ajax'])
				&& ($this->request['is_ajax'] == 'Y' || $this->request['is_ajax'] == 'N')
			) {
				$arParams['IS_AJAX'] = $this->request['is_ajax'] == 'Y';
			}
			else {
				$arParams['IS_AJAX'] = false;
			}
		}

		$arParams['ACTION'] = $this->getParam('ACTION', $arParams);


		$arParams['INCLUDE_OFFERS'] = $this->getParam('INCLUDE_OFFERS', $arParams);
		$arParams['USE_PRICES'] = $this->getParam('USE_PRICES', $arParams);

		if( isset($this->request['product_id']) ) {
			$arParams['PRODUCT_ID'] = $this->request['product_id'];
		}
		if(!is_array($arParams['PRODUCT_ID'] )) {
			if($arParams['PRODUCT_ID'] != "") {
				$arParams['PRODUCT_ID'] = explode(",", $arParams['PRODUCT_ID']);
				foreach($arParams['PRODUCT_ID'] as $key => $val) {
					$arParams['PRODUCT_ID'][$key] = trim($val);
				}
			}
			else {
				$arParams['PRODUCT_ID'] = [];
			}
		}

		return $arParams;
	}

	protected function getParam($name, $arParams)
	{
		if( isset($this->request[strtolower($name)]) && strlen($this->request[strtolower($name)]) > 0 ) {
			return strval($this->request[strtolower($name)]);
		}
		else {
			if( isset($arParams[strtoupper($name)]) && strlen($arParams[strtoupper($name)]) > 0 ) {
				return strval($arParams[strtoupper($name)]);
			}
			else {
				return '';
			}
		}
	}

	function executeComponent()
	{
		global $APPLICATION;

		if( $this->arParams['IS_AJAX'] ) {
			$APPLICATION->RestartBuffer();
		}

		if( !empty($this->arParams['ACTION']) ) {
			if( is_callable([$this, $this->arParams['ACTION'] . "Action"]) ) {
				try {
					call_user_func([$this, $this->arParams['ACTION'] . "Action"]);
				} catch( \Exception $e ) {
					$this->setError($e->getMessage());
				}
			}
		}

		if( count($this->errorCollection) ) {
			$this->arResponse['errors'] = $this->errorCollection;
		}

		if( $this->arParams['IS_AJAX'] ) {
			if( $this->getTemplateName() != '' ) {
				ob_start();
				$this->includeComponentTemplate();
				$this->arResponse['html'] = ob_get_contents();
				ob_end_clean();
			}
			header('Content-Type: application/json');
			echo json_encode($this->arResponse);
			$APPLICATION->FinalActions();
			die();
		}
		else {
			$this->getGifts();
			$this->includeComponentTemplate();
		}
	}

	protected function refreshAction()
	{
		$this->getGifts();
		if( count($this->errorCollection) ) {
			$this->setTemplateName('');
			$this->arResponse['html'] = "";
		}
	}

	protected function getGifts()
	{
		$this->arResult["ITEMS"] = [];
		if(!count($this->arParams['PRODUCT_ID'])) {
			return;
		}
		$giftIDs = [];
		foreach($this->arParams['PRODUCT_ID'] as $ID) {
			if ( $ID < 1) {
				$this->setError('Wrong product ID ('.$ID.')');
				return;
			}
			$res = \CIBlockElement::GetList(
				[],
				["ID" => (int)$ID],
				false,
				false,
				["ID", "IBLOCK_ID"]
			);
			if ( !$baseEl = $res->GetNext() ) {
				$this->setError('IBlock element not found (ID '.$ID.')');
				continue;
			}
			$giftIDs = array_merge($giftIDs, self::getGiftIds($ID));
		}
		if(count($giftIDs)) {
			$listres = \CIBlockElement::GetList(
				[],
				[ "ID" => $giftIDs, "=ACTIVE" => "Y", "=AVAILABLE" => "Y" ]
			);
			while ( $prEl = $listres->GetNext() ) {
				$prEl["PREVIEW_PICTURE"] = \CFile::GetFileArray($prEl["PREVIEW_PICTURE"]);
				$prEl["DETAIL_PICTURE"] = \CFile::GetFileArray($prEl["DETAIL_PICTURE"]);
				$propres = \CIBlockElement::GetProperty($prEl["IBLOCK_ID"], $prEl["ID"]);
				while($prop = $propres->Fetch()) {
					$prEl["PROPERTIES"][] = $prop;
				}
				$prEl["PRODUCT"] = \CCatalogProduct::GetByID($prEl["ID"]);
				if ($this->arParams["USE_PRICES"] == "Y") {
					$prEl["PRICE"] = CCatalogProduct::GetOptimalPrice($prEl["ID"])["RESULT_PRICE"];
					$prEl["PRICE"]["PRINT_BASE_PRICE"] = CurrencyFormat($prEl["PRICE"]["BASE_PRICE"], $prEl["PRICE"]["CURRENCY"]);
					$prEl["PRICE"]["PRINT_DISCOUNT_PRICE"] = CurrencyFormat($prEl["PRICE"]["DISCOUNT_PRICE"], $prEl["PRICE"]["CURRENCY"]);
				}
				$this->arResult["ITEMS"][] = $prEl;
			}
		}
	}


	/**
	 * Returns an array of all available gifts IDs for product
	 *
	 * @param int $productId - product ID
	 * @return array - gifts ID array
	 */
	public static function getGiftIds($productId)
	{
		$giftProductIds = [];

		if (!$productId) {
			return $giftProductIds;
		}

		DiscountCompatibility::stopUsageCompatible();

		$giftManager = Gift\Manager::getInstance();

		$potentialBuy = [
			'ID'                     => $productId,
			'MODULE'                 => 'catalog',
			'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
			'QUANTITY'               => 1,
		];

		$basket = Basket::loadItemsForFUser(Fuser::getId(), SITE_ID);

		$basketPseudo = $basket->copy();

		foreach ($basketPseudo as $basketItem) {
			$basketItem->delete();
		}

		$collections = $giftManager->getCollectionsByProduct($basketPseudo, $potentialBuy);

		foreach ($collections as $collection) {
			/** @var \Bitrix\Sale\Discount\Gift\Gift $gift */
			foreach ($collection as $gift) {
				$giftProductIds[] = $gift->getProductId();
			}
		}

		DiscountCompatibility::revertUsageCompatible();

		return $giftProductIds;
	}


	/**
	 * Setting error.
	 * @return boolean
	 */
	protected function setError($str, $code = 0)
	{
		$error = new \Bitrix\Main\Error($str, $code, "");
		return $this->errorCollection->setError($error);
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

}