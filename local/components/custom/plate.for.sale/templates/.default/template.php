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

echo '<pre>';
print_r($arResult);
echo '</pre>';
?>

<div class="plate-for-sale">
    <div class="plate-for-sale__win-word"><?php echo $arResult['WIN_WORD']; ?></div>

    <div class="plate-for-sale__wrap-plates">
        <?php foreach($arResult['PLATES_HASH'] as $key => $plate) : ?>
            <label class="plate-for-sale__plate">
                <input class="plate-for-sale__input js-plate-for-sale-input" type="checkbox" name="plate" value="<?= $plate ?>">
                <?= ++$key; ?>
            </label>
        <?php endforeach; ?>
    </div>



</div>
