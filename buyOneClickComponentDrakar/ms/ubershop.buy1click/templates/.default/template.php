<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?$this->createFrame()->begin("");?>

<?if(strlen($arResult["OK_MESSAGE"]) > 0):?>
	<div class="modal-header">
		<button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
		<div class="modal-title"><?=$arParams["TITLE"]?></div>
		<div class="modal-subtitle">
			<?=$arParams["OK_MESSAGE"]?>
		</div>
	</div>
	<script>
		$('.modal_ajax').on('hide.bs.modal', function (event) {
			window.location.reload();
		});
	</script>
<?else:?>
	<div class="modal-header">
		<button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">&times;</span></button>
		<div class="modal-title"><?=$arParams["TITLE"]?></div>
		<?if(!empty($arParams["DESCRIPTION"])):?><div class="modal-subtitle"><?=$arParams["DESCRIPTION"]?></div><?endif?>
	</div>

	<div class="modal-body">
		<form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data" id="form-<?=$arResult["RANDOM_ID"]?>" class="form">
			<?=bitrix_sessid_post()?>
			<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">

			<?foreach($arResult["FORM_FIELDS"] as $arItem):?>
				<?if(mb_substr($arItem["CODE"], 0, 7) == "HIDDEN_"):?>

					<?if($arItem["TYPE"] == "text"):?>
						<input id="field-<?=$arItem["HTML_ID"]?>" type="hidden" name="<?=$arItem["CODE"]?>" class="<?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>error<?endif?>" value="<?=$arItem["VALUE"]?>">
					<?elseif($arItem["TYPE"] == "textarea"):?>
						<textarea id="field-<?=$arItem["HTML_ID"]?>" class="hidden <?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>error<?endif?>" name="<?=$arItem["CODE"]?>" rows="5" cols="40"><?=$arItem["VALUE"]?></textarea>
					<?endif;?>

				<?elseif($arItem["CODE"] != "AGREE_TO_PROCESSING_DATA"):?>

					<div class="form-group field-type-<?=$arItem["TYPE"]?> field-<?=mb_strtolower($arItem["CODE"])?> <?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>has-error<?endif?>">

					<?
					if($arItem["TYPE"] != "checkbox" && $arItem["TYPE"] != "radio"){
						$fieldLabelFor = 'for="field-' . mb_strtolower($arItem["CODE"]) . '"';
					}
					else{
						$fieldLabelFor = '';
					}
					?>

					<label class="field-label" <?=$fieldLabelFor?>>
						<?=$arItem["NAME"]?> <?if($arItem["REQUIRED_FIELD"] == "Y"):?><span class="red_star">*</span><?endif?>
					</label>

					<div class="field-content clearfix">
						<?if($arItem["TYPE"] == "text"):?>

							<input id="field-<?=mb_strtolower($arItem["CODE"])?>" type="text" name="<?=$arItem["CODE"]?>" class="form-control <?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>error<?endif?>" value="<?=$arItem["VALUE"]?>">

							<?if($arItem['CODE'] == 'PHONE' && $arResult['ERROR_MESSAGE']['PHONE']['PHONE_NOT_VALID']):?>
								<div class="help-block"><?=$arResult['ERROR_MESSAGE']['PHONE']['PHONE_NOT_VALID']?></div>
							<?endif;?>

						<?elseif($arItem["TYPE"] == "textarea"):?>

							<textarea id="field-<?=mb_strtolower($arItem["CODE"])?>" class="form-control <?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>error<?endif?>" name="<?=$arItem["CODE"]?>" rows="5" cols="40"><?=$arItem["VALUE"]?></textarea>

						<?elseif($arItem["TYPE"] == "select" && !empty($arResult["PROPERTY_LIST"][$arItem["CODE"]])):?>

							<select id="field-<?=mb_strtolower($arItem["CODE"])?>" class="form-control" name="<?=$arItem["CODE"]?>">
								<?foreach($arResult["PROPERTY_LIST"][$arItem["CODE"]] as $arListItem):?>
									<?
									if($arListItem["ID"] == $arItem["VALUE"] || empty($arItem["VALUE"]) && $arListItem["DEF"] == "Y"){
										$active = ' selected="selected"';
									} else {
										$active = '';
									}
									?>
									<option value="<?=$arListItem["ID"]?>" <?=$active?>><?=$arListItem["VALUE"]?></option>
								<?endforeach?>
							</select>

						<?elseif($arItem["TYPE"] == "multiselect" && !empty($arResult["PROPERTY_LIST"][$arItem["CODE"]])):?>

							<select id="field-<?=mb_strtolower($arItem["CODE"])?>" class="form-control" name="<?=$arItem["CODE"]?>[]" multiple="multiple">
								<?foreach($arResult["PROPERTY_LIST"][$arItem["CODE"]] as $arListItem):?>
									<?
									if(in_array($arListItem["ID"],$arItem["VALUE"]) || empty($arItem["VALUE"]) && $arListItem["DEF"] == "Y"){
										$active = ' selected="selected"';
									} else {
										$active = '';
									}
									?>
									<option value="<?=$arListItem["ID"]?>" <?=$active?>><?=$arListItem["VALUE"]?></option>
								<?endforeach?>
							</select>

						<?elseif($arItem["TYPE"] == "radio" && !empty($arResult["PROPERTY_LIST"][$arItem["CODE"]])):?>

							<?foreach($arResult["PROPERTY_LIST"][$arItem["CODE"]] as $arListItem):?>
								<div class="radio">
									<label>
										<?
										if($arListItem["ID"] == $arItem["VALUE"] || empty($arItem["VALUE"]) && $arListItem["DEF"] == "Y"){
											$active = ' checked="checked"';
										} else{
											$active = '';
										}
										?>
										<input type="radio" name="<?=$arItem["CODE"]?>" value="<?=$arListItem["ID"]?>" <?=$active?>>
										<?=$arListItem["VALUE"]?>
									</label>
								</div>
							<?endforeach?>

						<?elseif($arItem["TYPE"] == "checkbox" && !empty($arResult["PROPERTY_LIST"][$arItem["CODE"]])):?>

							<?foreach($arResult["PROPERTY_LIST"][$arItem["CODE"]] as $arListItem):?>
								<div class="checkbox">
									<label>
										<?
										if(in_array($arListItem["ID"],$arItem["VALUE"]) || empty($arItem["VALUE"]) && $arListItem["DEF"] == "Y"){
											$active = 'checked="checked"';
										} else {
											$active = '';
										}
										?>
										<input type="checkbox" name="<?=$arItem["CODE"]?>[]" value="<?=$arListItem["ID"]?>" <?=$active?>>
										<?=$arListItem["VALUE"]?>
									</label>
								</div>
							<?endforeach?>

						<?elseif($arItem["TYPE"] == "file"):?>
							<div class="field-content-in">
								<span class='btn btn-sm btn-dark upload-button'>
									<?=GetMessage("AC_SF_FILE")?>
									<input id="field-<?=mb_strtolower($arItem["CODE"])?>" type="file" name="<?=$arItem["CODE"]?>" size="40" onchange="fileUploadSetName(this);">
								</span>
								<span class="badge uploaded-file-info"></span>
							</div>

							<?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>
								<div class="field-error">
									<?=$arResult["ERROR_MESSAGE"][$arItem["CODE"]]?>
								</div>
							<?endif?>

						<?elseif($arItem["TYPE"] == "Date" || $arItem["TYPE"] == "DateTime"):?>
							<?
							$APPLICATION->IncludeComponent("bitrix:main.calendar", "calendar",
								array(
									"SHOW_INPUT" => "Y",
									"FORM_NAME" => "",
									"INPUT_NAME" => $arItem["CODE"],
									"INPUT_NAME_FINISH" => ($arItem["MULTIPLE"] == "Y" ? $arItem["CODE"] . "_FINISH" : ""),
									"INPUT_VALUE" => $arItem["INPUT_VALUE"],
									"INPUT_VALUE_FINISH" => $arItem["INPUT_VALUE_FINISH"],
									"SHOW_TIME" => ($arItem["TYPE"] == "DateTime" ? "Y" : "N"),
									"HIDE_TIMEBAR" => "N",
									"INPUT_ADDITIONAL_ATTR" => "placeholder='".($arItem["PLACEHOLDER"])."'"
								),
								false
							);
							?>
						<?endif?>
					</div>
				</div>

				<?endif;?>
			<?endforeach?>

			<?if($arResult["AGREE_TO_PROCESSING_DATA"] == "Y"):?>
				<?
				$arItem = $arResult["FORM_FIELDS"]["AGREE_TO_PROCESSING_DATA"];
				?>
				<div class="form-group field-type-checkbox field-accept <?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>field-accept-error<?endif;?>">
					<div class="field-content clearfix">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="AGREE_TO_PROCESSING_DATA" <?if(!empty($arItem["VALUE"])):?>checked="checked"<?endif;?>>
								<?=str_replace('#link#', $arResult["AGREE_TO_PROCESSING_DATA_LINK"], GetMessage("AC_SF_152_FZ"))?>
							</label>

							<?if($arResult["ERROR_MESSAGE"][$arItem["CODE"]]):?>
								<div class="field-error accept-error">
									<?=$arResult["ERROR_MESSAGE"][$arItem["CODE"]]?>
								</div>
							<?endif;?>
						</div>
					</div>
				</div>
			<?endif;?>

			<?if($arResult["USE_CAPTCHA"] == "BITRIX_CAPTCHA"):?>
				<div class="form-group captcha-block <?if($arResult["ERROR_MESSAGE"]["CAPTCHA"]):?>has-error<?endif?>">
					<label><?=GetMessage("AC_SF_CAPTCHA_CODE")?> <span class="red_star">*</span></label>
					<div class="form-input">
						<img class="captcha-img" src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">
						<!-- <a class="btn-captcha-update" href="javascript:void(0);" onclick="updateCaptcha(this);" title="<?=GetMessage("AC_SF_REFRESH")?>">
							<i class="fa fa-refresh"></i>
						</a> -->
						<input class="captcha-input form-control" type="text" name="captcha_word" size="30" maxlength="50" value="">
						<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>">
					</div>
				</div>
			<?elseif($arResult["USE_CAPTCHA"] == "RECAPTCHA"):?>
				<div class="form-group captcha-block <?if($arResult["ERROR_MESSAGE"]["CAPTCHA"]):?>has-error<?endif?>">
						<label><?=GetMessage("AC_SF_CAPTCHA_CODE")?> <span class="red_star">*</span></label>
						<div class="form-input">
							<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>">
							<input class="captcha-input form-control" type="text" name="captcha_word" size="30" maxlength="50" value="">
							<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>">
						</div>
					</div>
			<?endif;?>

			<div class="form-footer">
				<?if ($arResult['ORDER_FAIL']):?>
					<div class="alert alert-danger" role="alert"><?=$arResult['ORDER_FAIL']?></div>
				<?endif;?>

				<div class="col-submit">
					<input type="submit" class="btn btn-primary" name="submit" value="<?=GetMessage("AC_SF_SUBMIT")?>">
				</div>
				<div class="col-required">
					<span class="red_star">*</span> <span class="required-text"><?= GetMessage("AC_SF_REQUIRED") ?></span>
				</div>
			</div>
		</form>
	</div>

	<script>
		BX.ready(function(){
			removeFieldErrorOnChange();
		});
	</script>

<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
