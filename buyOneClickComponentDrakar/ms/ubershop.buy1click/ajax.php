<?php
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NOT_CHECK_PERMISSIONS', true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!Bitrix\Main\Loader::includeModule('sale'))
	return;

if ($request->get('CLEAR_CART') == 'Y') {
	$response = [
		"STATUS" => "OK",
	];

	$data = ob_get_clean();
	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json');

	$clearCart = \CSaleBasket::DeleteAll(\CSaleBasket::GetBasketUserID());

	if ($clearCart) {
		$response = [
			"STATUS" => "OK",
		];
	}
	else {
		$response = [
			"STATUS" => "ERROR",
		];
	}

	echo Bitrix\Main\Web\Json::encode($response);
	die;
}
