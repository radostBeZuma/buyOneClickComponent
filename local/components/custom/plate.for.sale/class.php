<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CPlateForSale extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams): array
    {
        return $arParams;
    }

    public function calcChanceOutputWord($percent): bool
    {
        $chance = mt_rand(1, 100);
        return $chance <= intval($percent);
    }

    public function checkPartsExceedTiles($numPlates, $numParts): bool
    {
        return $numPlates < $numParts;
    }

    public function splitWordIntoParts($word, $numParts): array
    {
        $length = mb_strlen($word);
        $partSize = intval($length / $numParts);
        $remainder = $length % $numParts;

        $parts = [];
        $start = 0;

        for ($i = 0; $i < $numParts; $i++) {
            $currentPartSize = $partSize + ($i < $remainder ? 1 : 0);
            $parts[] = mb_substr($word, $start, $currentPartSize);
            $start += $currentPartSize;
        }

        return $parts;
    }

    public function checkWordNotDivisibility($partsWinWord)
    {
        foreach ($partsWinWord as $part) {
            if (empty($part)) {
                return true;
            }
        }

        return false;
    }

    function getRandomRussianWords($count)
    {
        $words = [];

        $file = $_SERVER['DOCUMENT_ROOT'] . $this->getPath() . '/russian_words.txt';
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $randomIndexes = array_rand($lines, $count);

        foreach ($randomIndexes as $index) {
            $words[] = $lines[$index];
        }

        unset($lines);
        return $words;
    }

    public function getRawPlates($shouldDisplayWord, $partsWinWord, $numPlates): array
    {
        $plates = [];

        if ($shouldDisplayWord) {
            $plates = array_merge($plates, $partsWinWord);
        }

        // TODO: По факту может быть такое что не хватит слов чтобы заполнить плитки

        if (count($plates) !== $numPlates) {
            $words = $this->getRandomRussianWords(10);

            foreach ($words as $word) {
                $partsWord = $this->splitWordIntoParts($word, 5);

                foreach ($partsWord as $part) {
                    if (!empty($part)) {
                        $plates[] = $part;

                        if (count($plates) === $numPlates) {
                            break 2;
                        }
                    }
                }
            }
        }

        if (count($plates) === $numPlates) {
            shuffle($plates);
        }

        return $plates;
    }

    public function getHashPlates($platesRaw, $secretKey)
    {
        $hashPlates = [];

        foreach ($platesRaw as $plate) {
            $hashPlates[] = hash_hmac('sha256', $plate, $secretKey);
        }

        return $hashPlates;
    }

    public function executeComponent()
    {
        $numPartsWord = (int) $this->arParams['NUM_PARTS_WORD'];
        $numPlates = (int) $this->arParams['NUM_PLATES'];
        $percentWordDisplay = (int) $this->arParams['PERCENT_WORD_DISPLAY'];
        $word = $this->arParams['WORD'];
        $secretKey = $this->arParams['SECRET_KEY'];

        $partsWinWord = $this->splitWordIntoParts($word, $numPartsWord);

        if ($this->checkPartsExceedTiles($numPlates, $numPartsWord)) {
            ShowError('Количество частей слова превышает количество плиток');
            return;
        }

        if ($this->checkWordNotDivisibility($partsWinWord)) {
            ShowError('Слово не может быть поделено на столько частей');
            return;
        }

        $shouldDisplayWord = $this->calcChanceOutputWord($percentWordDisplay);

        $platesRaw = $this->getRawPlates($shouldDisplayWord, $partsWinWord, $numPlates);

        $platesHash = $this->getHashPlates($platesRaw, $secretKey);

        $this->arResult = [
            'WIN_WORD' => $word,
            'SHOULD_DISPLAY_WORD' => $shouldDisplayWord,
            'PLATES_RAW' => $platesRaw,
            'PLATES_HASH' => $platesHash,

        ];

        $this->includeComponentTemplate();
    }
}