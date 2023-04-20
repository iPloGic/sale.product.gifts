<?
/** @global \CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

$parameters = $request->get('parameters');
$parameters["IS_AJAX"] = "Y";

$APPLICATION->IncludeComponent(
	'iplogic:sale.product.gifts',
	$request->get('template'),
	$parameters,
	false
);