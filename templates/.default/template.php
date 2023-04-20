<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var customComponent $component */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

foreach($arParams as $key => $val) {
	if(substr($key,0,1) == "~")
		continue;
	if($key == "ACTION")
		continue;
	/*if($key == "PRODUCT_ID")
		continue;*/
	$arParameters[$key] = $val;
}
$loader = file_get_contents($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/img/loader.svg");
$arJsParams = [
	"parameters" => $arParameters,
	"siteId" => SITE_ID,
	"template" => $templateName,
	"componentPath" => $componentPath,
	"activator" => "el_offers_el",   // class
	"offer_id_attr" => "data-id",    // acrivator attr
	"loader" => '<div class="ipl-spg-loader">' . $loader . '</div>',
];

$gift_fst = true;

//echo "<pre>"; print_r($arParams); echo "</pre>";
//echo "<pre>"; print_r($arResult); echo "</pre>";

if (!$arParams['ACTION']) { ?>
<div class="ipl-spg-component-wrapper">
<? }
if(count($arResult["ITEMS"])) {?>
	<div class="ipl-spg-gift-list">
		<div class="ipl-spg-gift-head"><?=$arParams["TITLE"]?></div>
		<div class="ipl-spg-gift-products">
			<? $firstRrod = true;
			foreach($arResult["ITEMS"] as $arItem) {
				?>
				<div class="ipl-spg-product<?=($firstRrod ? " ipl-spg-product-checked" : "")?>"
				     data-id="<?=$arItem["ID"]?>"
				>
					<div class="ipl-spg-product-img" style="background-image: url('<?=$arItem["DETAIL_PICTURE"]["SRC"]?>')" title="<?=$arItem["NAME"]?>"></div>
					<div class="ipl-spg-price"><?=$arItem["PRICE"]["DISCOUNT_PRICE"]?> ₽</div>
					<div class="ipl-spg-free"><?=Loc::getMessage('IPL_SPG_FREE')?></div>
					<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" target="_blank">
						<?=Loc::getMessage('IPL_SPG_ABOUT')?>
					</a>
				</div>
				<?
				$firstRrod = false;
			} ?>
		</div>
	</div>
<?}
if (!$arParams['ACTION']) { ?>
</div>
<br><br>
<script>
	window.oJCSaleProductGiftsComponent = new JCSaleProductGiftsComponent(<?=CUtil::PhpToJSObject($arJsParams)?>)
</script>
<? }