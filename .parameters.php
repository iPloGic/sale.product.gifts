<? if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true ) {
	die();
}

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Loader;

$arComponentParameters = [

	"PARAMETERS" => [

		"PRODUCT_ID" => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_PRODUCT_ID"),
			"TYPE"    => "STRING",
			"DEFAULT" => '',
		],

		"TITLE" => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_TITLE"),
			"TYPE"    => "STRING",
			"DEFAULT" => Loc::getMessage("PARAMETER_TITLE_DEFAULT"),
		],

		'USE_PRICES' => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_USE_PRICES"),
			"TYPE"    => "CHECKBOX",
			"DEFAULT" => 'Y',
			"REFRESH" => "Y",
			"SORT"    => 10,
		],

		"CACHE_TIME" => [
			"DEFAULT" => 36000,
			"PARENT"  => "CACHE_SETTINGS",
		],
		"CACHE_TYPE" => [
			"PARENT"            => "CACHE_SETTINGS",
			"NAME"              => Loc::getMessage("COMP_PROP_CACHE_TYPE"),
			"TYPE"              => "LIST",
			"VALUES"            => [
				"A" => Loc::getMessage("COMP_PROP_CACHE_TYPE_AUTO") . " " . Loc::getMessage("COMP_PARAM_CACHE_MAN"),
				"Y" => Loc::getMessage("COMP_PROP_CACHE_TYPE_YES"),
				"N" => Loc::getMessage("COMP_PROP_CACHE_TYPE_NO"),
			],
			"DEFAULT"           => "N",
			"ADDITIONAL_VALUES" => "N",
			"REFRESH"           => "Y"
		],
	],
];

//}

?>
