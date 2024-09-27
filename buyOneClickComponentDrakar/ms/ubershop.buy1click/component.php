<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main,
		Bitrix\Main\Loader,
		Bitrix\Main\Config\Option,
		Bitrix\Sale,
        Bitrix\Sale\Delivery,
		Bitrix\Sale\Order,
		Bitrix\Main\Application,
		Bitrix\Sale\DiscountCouponsManager,
		Bitrix\Main\PhoneNumber\Parser,
		Bitrix\Main\PhoneNumber\Format;

global $USER, $APPLICATION;


if(!\Bitrix\Main\Loader::includeModule("sale"))
{
	ShowError(GetMessage('ERROR_MODULE_SALE_IS_NOT_INSTALLED'));
	return;
}

if(!function_exists('randomPassword')) {
	function randomPassword($length = 8) {
		return mb_substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-=+;:,.?', ceil($length/strlen($x)) )),1,$length);
	}
}

// function generateRandomString($length = 10) {
// 	return mb_substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
// };

// function generateRandomStringLower($length = 10) {
// 	return mb_substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)) )),1,$length);
// };

// function generateRandomEmail() {
// 	return generateRandomStringLower().'@'.generateRandomStringLower().'.ru';
// };

// function randomNumber($length) {
// 	$result = '';
// 	for($i = 0; $i < $length; $i++) {
// 		$result .= mt_rand(0, 9);
// 	}
// 	return $result;
// }

// function generateRandomPhone() {
// 	return '+7495'.randomNumber(7);
// };

// function generateRandomName() {
// 	return generateRandomString(5).' '.generateRandomString(5);
// };


// ms_p(generateRandomName());
// ms_p(randomPassword());
// ms_p(generateRandomEmail());
// ms_p(generateRandomPhone());


$getPropertyByCode = function($propertyCollection, $code)
{
	foreach ($propertyCollection as $property)
	{
		if($property->getField('CODE') == $code)
			return $property;
	}
};

$request = Application::getInstance()->getContext()->getRequest();
$siteId = \Bitrix\Main\Context::getCurrent()->getSite();
$currencyCode = Option::get('sale', 'default_currency', 'RUB');

$newUserEmailUniqCheck = Option::get('main', 'new_user_email_uniq_check');

$useCaptcha = mb_strtoupper(CUberShop::getOption('use_captcha', 'N'));
$personId = CUberShop::getOption('buy_1_click_person', '1');
$deliveryId = CUberShop::getOption('buy_1_click_delivery', '1');
$paymentMethodId = CUberShop::getOption('buy_1_click_payment', '1');

$personType = Sale\PersonType::getList(['filter' => ['=ID' => $personId]])->fetchAll();
$userConsentEnable = CUberShop::getOption('user_consent_enable', '');

// Get person type by id
$dbRes = \Bitrix\Sale\Internals\BusinessValuePersonDomainTable::getList([
	'filter' => ['=PERSON_TYPE_ID' => $personId]
]);
$personType = '';
$data = $dbRes->fetch();
if (isset($data['DOMAIN']))
{
	$personType = $data['DOMAIN'];
}
unset($data);

$arResult = [
	// 'TITLE' => 'TITLE',
	// 'DESCRIPTION' => 'DESCRIPTION',
	'AGREE_TO_PROCESSING_DATA' => $userConsentEnable,
	'AGREE_TO_PROCESSING_DATA_LINK' => '/policy/',
	'FORM_FIELDS' => [],
	'ERROR_MESSAGE' => [],
	'OK_MESSAGE' => false,
	'ORDER_FAIL' => false,
	'PARAMS_HASH' => md5(serialize($arParams).$this->GetTemplateName()),
	'USE_CAPTCHA' => $useCaptcha,
];

if($arResult["USE_CAPTCHA"] == "BITRIX_CAPTCHA" || $arResult["USE_CAPTCHA"] == "RECAPTCHA"){
	$arResult["capCode"] =  $APPLICATION->CaptchaGetCode();
}

$arResult['FORM_FIELDS'][] = [
	"CODE" => "FIO",
	"NAME" => GetMessage('FIELD_FIO'),
	"REQUIRED_FIELD" => "Y",
	"VALUE" => "",
	// "VALUE" => generateRandomName(),
	"TYPE" => "text",
];

$arResult['FORM_FIELDS']['AGREE_TO_PROCESSING_DATA'] = [
	"CODE" => "AGREE_TO_PROCESSING_DATA",
	"NAME" => "",
	"REQUIRED_FIELD" => $userConsentEnable,
	"VALUE" => CUberShop::getOption('user_consent_check_default', ''),
	"TYPE" => "checkbox",
];

$arResult['FORM_FIELDS'][] = [
	"CODE" => "PHONE",
	"NAME" => GetMessage('FIELD_PHONE'),
	"REQUIRED_FIELD" => "Y",
	"VALUE" => "",
	// "VALUE" => generateRandomPhone(),
	"TYPE" => "text",
];

$arResult['FORM_FIELDS'][] = [
	"CODE" => "EMAIL",
	"NAME" => GetMessage('FIELD_EMAIL'),
	"REQUIRED_FIELD" => "Y",
	"VALUE" => "",
	// "VALUE" => generateRandomEmail(),
	"TYPE" => "text",
];

$arResult['FORM_FIELDS'][] = [
	"CODE" => "USER_DESCRIPTION",
	"NAME" => GetMessage('FIELD_DESCRIPTION'),
	"REQUIRED_FIELD" => "N",
	"VALUE" => "",
	"TYPE" => "textarea",
];

$requestFields = $request->getPostList();
$fieldValues = [];

if (count($requestFields)) {
	foreach ($arResult['FORM_FIELDS'] as $key => $value) {
		if ( !empty($requestFields[$value['CODE']]) ) {
			$fieldValues[$value['CODE']] = $requestFields[$value['CODE']];
			$arResult['FORM_FIELDS'][$key]['VALUE'] = $requestFields[$value['CODE']];
		}
		else {
			$arResult['FORM_FIELDS'][$key]['VALUE'] = '';
		}
	}
}

// FORM VALIDATION
if($_SERVER["REQUEST_METHOD"] == "POST" && $request->getPost("submit") != '' && ($request->getPost("PARAMS_HASH") !== null || $arResult["PARAMS_HASH"] === $request->getPost("PARAMS_HASH")))
{
	$arResult["ERROR_MESSAGE"] = array();
	if(check_bitrix_sessid())
	{

		// CREATING ERROR MESSAGES
		foreach($arResult["FORM_FIELDS"] as $arItem)
		{
			if($arItem["CODE"] == "AGREE_TO_PROCESSING_DATA") {
				if (empty($arItem["VALUE"]) && $userConsentEnable) {
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]] = GetMessage("152_FZ_ERROR");
				}
			}
			elseif($arItem["CODE"] == "EMAIL") {
				if($arItem["REQUIRED_FIELD"] == "Y" && !check_email($request->getPost($arItem["CODE"]))) {
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]] = GetMessage("REQ_EMAIL");
				}
				elseif(strlen($request->getPost($arItem["CODE"])) > 1 && !check_email($request->getPost($arItem["CODE"]))){
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]] = GetMessage("EMAIL_NOT_VALID");
				}
			}
			elseif($arItem["CODE"] == "PHONE") {
				$phone = $request->getPost($arItem["CODE"]);
				$phoneParse = Parser::getInstance()->parse($request->getPost($arItem["CODE"]));
				$phoneIsValid = $phoneParse->isValid();

				if ($arItem["REQUIRED_FIELD"] == "Y" && !$phone) {
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]]['PHONE_REQ'] = GetMessage("PHONE_REQ");
				}
				elseif (!$phoneIsValid) {
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]]['PHONE_NOT_VALID'] = GetMessage("PHONE_NOT_VALID");
				}
			}
			elseif($arItem["TYPE"] == "multiselect" || $arItem["TYPE"] == "checkbox" || $arItem["TYPE"] == "radio" && $arItem["REQUIRED_FIELD"] == "Y") {
				if(empty($arItem["VALUE"])){
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]] = GetMessage("REQ_FIELD");
				}
			}
			else {
				if($arItem["REQUIRED_FIELD"] == "Y" && strlen($request->getPost($arItem["CODE"])) <= 1) {
					$arResult["ERROR_MESSAGE"][$arItem["CODE"]] = GetMessage("REQ_FIELD");
				}
			}
		}

		if($arResult["USE_CAPTCHA"] == "BITRIX_CAPTCHA" || $arResult["USE_CAPTCHA"] == "RECAPTCHA")
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			// $captcha_code = $request->getPost("captcha_sid");
			// $captcha_word = $request->getPost("captcha_word");
			
			$captcha_code = $_POST["captcha_sid"];
			$captcha_word = $_POST["captcha_word"];

			$cpt = new CCaptcha();
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (strlen($captcha_word) > 0 && strlen($captcha_code) > 0)
			{
				if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
					$arResult["ERROR_MESSAGE"]["CAPTCHA"] = GetMessage("CAPTCHA_WRONG");
			}
			else
				$arResult["ERROR_MESSAGE"]["CAPTCHA"] = GetMessage("CAPTHCA_EMPTY");
		}

		// Proceed order
		if (empty($arResult["ERROR_MESSAGE"]))
		{

			$registeredUserID = $USER->GetID();

			if (!$registeredUserID) {
				// Search registred user with email
				$order = array('sort' => 'asc');
				$tmp = 'sort';
				$filter = array('=EMAIL' => $fieldValues['EMAIL']);
				$rsUsers = CUser::GetList($order, $tmp, $filter);
				$rowCount = $rsUsers->SelectedRowsCount();

				if ($rowCount === 1) {
					while ($arUser = $rsUsers->Fetch()) {
						$registeredUserID = $arUser['ID'];
						break;
					}
				}
				
				// Auto register new user
				if (!$registeredUserID) {
					// $captcha_sid = $APPLICATION->CaptchaGetCode();
					$captcha_sid = $arResult["capCode"];

					$dbRes = \Bitrix\Main\Application::getConnection()->query("SELECT CODE FROM b_captcha WHERE id='".$captcha_sid."'");
					if($res = $dbRes->fetch()) {
						if($res['CODE']) {
							$captcha_word = $res['CODE'];
						}
					}

					if ($newUserEmailUniqCheck) {
						$login = $fieldValues['EMAIL'];
					}
					else {
						$login = 'user_'.time().mt_rand(100, 999);
					}
					$password = randomPassword();

					$fio = trim($fieldValues['FIO']);
					$fio = str_replace('  ', ' ', $fio);

					if(mb_substr_count($fio, ' ') == 0) {
						$name = $fio;
					}
					else {
						$fioArr = explode(' ', $fio);
						$last_name = $fioArr[0] ? $fioArr[0] : '';
						$name = $fioArr[1] ? $fioArr[1] : '';
						$second_name = $fioArr[2] ? $fioArr[2] : '';
					}
					
					$fields = [
						"LOGIN" => $login,
						"NAME" => $name,
						"LAST_NAME" => $last_name,
						"SECOND_NAME" => $second_name,
						"EMAIL" => $fieldValues['EMAIL'],
						"PASSWORD" => $password,
						"CONFIRM_PASSWORD" => $password,
						'ACTIVE' => 'Y',
						'LID' => $siteId,
						'PHONE_NUMBER' => $phone,
						'PERSONAL_PHONE' => $phoneParse->format(Format::E164),
					];

					$user = new CUser;
					$userResult = $user->Add($fields);

					if (intval($userResult) <= 0) {
						$errorMessage = ((strlen($user->LAST_ERROR) > 0) ? $user->LAST_ERROR : '');
						$errorMessage = str_replace(GetMessage('REPLACE_ERROR_LOGIN'), 'E-mail', $errorMessage);
						$arResult["ORDER_FAIL"] = GetMessage('ERROR').': '. $errorMessage;
					}
					else {
						$USER->Authorize($userResult);
						$registeredUserID = $userResult;

						CUser::SendUserInfo($registeredUserID, $siteId, GetMessage('INFO_REQ'), true);
					}
				}
			}


			if(!$arResult["ORDER_FAIL"])
			{
				DiscountCouponsManager::init();

				// order one product
				if ($product_id = $request->getQuery("product_id"))
				{
					$quantity = (int)$request->getQuery("quantity");
					if (!$quantity) {
						$quantity = 1;
					}
					$basket = \Bitrix\Sale\Basket::create($siteId);
					$item = $basket->createItem('catalog', $product_id);
					$item->setFields(array(
						'QUANTITY' => $quantity,
						'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
						'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
						'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
					));
					// $basket->save();
					$basket = $basket->getOrderableItems();
				}
				// order all basket
				else {
					$basket = Sale\Basket::loadItemsForFUser(\CSaleBasket::GetBasketUserID(), Bitrix\Main\Context::getCurrent()->getSite())->getOrderableItems();
				}

				if ($registeredUserID) {
					$order = Order::create($siteId, $registeredUserID);
				}
				else {
					$order = Order::create($siteId);
				}
				$order->setPersonTypeId($personId);
				$order->setBasket($basket);


				// Delivery

                $delivery = CSaleDelivery::GetByID($deliveryId);

                if (intval($deliveryId) === 1) {
                    $delivery = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
                }

				$shipmentCollection = $order->getShipmentCollection();
				$shipment = $shipmentCollection->createItem();
				$shipment->setFields(array(
					'DELIVERY_ID' => $delivery['ID'],
					'DELIVERY_NAME' => $delivery['NAME'],
					'CURRENCY' => $order->getCurrency()
				));

				$shipmentItemCollection = $shipment->getShipmentItemCollection();
				foreach ($order->getBasket() as $item)
				{
					$shipmentItem = $shipmentItemCollection->createItem($item);
					$shipmentItem->setQuantity($item->getQuantity());
				}


				// Payment
				$paymentMethod = CSalePaySystemAction::GetByID($paymentMethodId);
				$paymentCollection = $order->getPaymentCollection();
				$extPayment = $paymentCollection->createItem();
				$extPayment->setFields(array(
					'PAY_SYSTEM_ID' => $paymentMethod['ID'],
					'PAY_SYSTEM_NAME' => $paymentMethod['NAME'],
					'SUM' => $order->getPrice()
				));

				$order->doFinalAction(true);


				// Properties
				$propertyCollection = $order->getPropertyCollection();

				// set props
				if ($personType == 'E') {
					$property = $getPropertyByCode($propertyCollection, 'CONTACT_PERSON');
					$property->setValue($fieldValues['FIO']);

				}
				else {
					//$property = $getPropertyByCode($propertyCollection, 'FIO');
					$property = $getPropertyByCode($propertyCollection, 'NAME');
					$property->setValue($fieldValues['FIO']);
				}

				$property = $getPropertyByCode($propertyCollection, 'EMAIL');
				$property->setValue($fieldValues['EMAIL']);

				$property = $getPropertyByCode($propertyCollection, 'PHONE');
				$property->setValue($fieldValues['PHONE']);


				// set fields
				$order->setField('CURRENCY', $currencyCode);
				$order->setField('COMMENTS', GetMessage('ORDER_COMMENT'));
				$order->setField('USER_DESCRIPTION', $fieldValues['USER_DESCRIPTION']);

				$order->save();
				$orderId = $order->GetId();
				if($orderId > 0){
                    $arBasketItems = [];

                    foreach ($basket as $basketItem) {
                        $arBasketItems[] = [
                            'PRODUCT_NAME'  => $basketItem->getField('NAME'),
                            'QUANTITY'      => intval($basketItem->getQuantity()),
                            'PRICE'         => ($basketItem->getPrice()) ? intval($basketItem->getPrice()) : 0,
                        ];
                    }

					$arResult["OK_MESSAGE"] = GetMessage("OK_MESSAGE");

                    $basket = $order->getBasket();

                    $event = new \Bitrix\Main\Event("msUbershop", "OnAfterSaveBuy1Click", [
                        'FORM_FIELDS'               => $fieldValues,
                        'BASKET_ITEMS'              => $basket->getListOfFormatText(),
                        'BASKET_ITEMS_FORMATTED'    => $arBasketItems,
                    ]);

                    $event->send();
				}
				else{
					$arResult["ORDER_FAIL"] = GetMessage("ORDER_FAIL");
				}
			}

		}
	}
	else{
		$arResult["ERROR_MESSAGE"][] = GetMessage("SESS_EXP");
	}
}
elseif($_REQUEST["success"] == $arResult["PARAMS_HASH"])
{
	$arResult["OK_MESSAGE"] = $arParams["OK_TEXT"];
}

$this->IncludeComponentTemplate();
