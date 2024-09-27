<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();

$arComponentDescription = array(
	"NAME" => GetMessage("US_B1C_COMPONENT_NAME"),
	"DESCRIPTION" => "",
	"PATH" => array(
		"ID" => "ubershop",
		"NAME" => GetMessage("US_COMPONENT_CATEGORY"),
		"CHILD" => array(
		 "ID" => "utils",
		 "NAME" => GetMessage("US_COMPONENT_UTILS"),
		)
	),
);
