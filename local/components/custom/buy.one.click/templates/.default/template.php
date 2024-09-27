<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

/*echo '<pre>';
print_r($arResult);
echo '</pre>';*/
?>

<form action="<?= POST_FORM_ACTION_URI ?>" method="POST" enctype="multipart/form-data">
    <?php foreach($arResult['ORDER_PROPS'] as $arProp) :?>
        <div>
            <label>
                <?= $arProp['NAME'] ?>
                <?php if($arProp['CODE'] == 'COMMENT') : ?>
                    <textarea name="<?= $arProp['CODE'] ?>"></textarea>
                <?php else : ?>
                    <input type="text" name="<?= $arProp['CODE'] ?>">
                <?php endif; ?>
            </label>
        </div>
    <?php endforeach; ?>

    <input type="submit" value="text">
</form>


