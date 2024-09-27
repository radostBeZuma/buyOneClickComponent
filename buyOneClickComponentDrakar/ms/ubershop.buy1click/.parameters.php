<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters["PARAMETERS"]["TITLE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("TITLE"),
  "TYPE" => "STRING",
  "DEFAULT" => GetMessage("TITLE_DEFAULT"),
);

$arComponentParameters["PARAMETERS"]["DESCRIPTION"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("DESCRIPTION"),
	"TYPE" => "STRING",
	"DEFAULT" => GetMessage("DESCRIPTION_DEFAULT"),
);

$arComponentParameters["PARAMETERS"]["OK_MESSAGE"] = array(
	"NAME"    => GetMessage("OK_MESSAGE"),
	"TYPE"    => "STRING",
	"DEFAULT" => GetMessage("OK_MESSAGE_DEFAULT"),
	"PARENT"  => "BASE",
);

