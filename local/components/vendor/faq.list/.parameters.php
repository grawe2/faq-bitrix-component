<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arCurrentValues */

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\TypeTable;

if (!Loader::includeModule('iblock')) {
	return;
}

$arIBlockTypes = [];
$arIBlocks = [];
$arSections = [];

// Получаем типы инфоблоков
$dbIBlockTypes = TypeTable::getList([
	'select' => array('*', 'NAME' => 'LANG_MESSAGE.NAME'),
	'filter' => array('=LANG_MESSAGE.LANGUAGE_ID' => 'ru')
]);
while ($arType = $dbIBlockTypes->fetch()) {
	$arIBlockTypes[$arType['ID']] = $arType['NAME'];
}


// Получаем инфоблоки по выбранному типу
if (!empty($arCurrentValues['IBLOCK_TYPE'])) {
	$dbIBlocks = IblockTable::getList([
		'select' => ['ID', 'NAME'],
		'filter' => [
			'IBLOCK_TYPE_ID' => $arCurrentValues['IBLOCK_TYPE'],
			'ACTIVE' => 'Y',
		],
	]);
	while ($arIBlock = $dbIBlocks->fetch()) {
		$arIBlocks[$arIBlock['ID']] = '[' . $arIBlock['ID'] . '] ' . $arIBlock['NAME'];
	}
}

// Получаем разделы по выбранному инфоблоку
if (!empty($arCurrentValues['IBLOCK_ID'])) {
	$dbSections = SectionTable::getList([
		'select' => ['ID', 'NAME'],
		'filter' => [
			'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
			'ACTIVE' => 'Y',
		],
	]);
	while ($arSection = $dbSections->fetch()) {
		$arSections[$arSection['ID']] = '[' . $arSection['ID'] . '] ' . $arSection['NAME'];
	}
}

$arComponentParameters = [
	'PARAMETERS' => [
		'IBLOCK_TYPE' => [
			'PARENT' => 'BASE',
			'NAME' => 'Тип инфоблока',
			'TYPE' => 'LIST',
			'VALUES' => $arIBlockTypes,
			'REFRESH' => 'Y',
		],
		'IBLOCK_ID' => [
			'PARENT' => 'BASE',
			'NAME' => 'Инфоблок',
			'TYPE' => 'LIST',
			'VALUES' => $arIBlocks,
			'REFRESH' => 'Y',
		],
		'SECTION_ID' => [
			'PARENT' => 'BASE',
			'NAME' => 'Раздел',
			'TYPE' => 'LIST',
			'VALUES' => $arSections,
		],
		'CACHE_TYPE' => [
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => 'Тип кеша',
			'TYPE' => 'LIST',
			'VALUES' => [
				'A' => 'Автоматически',
				'Y' => 'Включён',
				'N' => 'Выключен',
			],
			'DEFAULT' => 'A',
		],
		'CACHE_TIME' => [
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => 'Время кеша (сек)',
			'TYPE' => 'STRING',
			'DEFAULT' => '3600',
		],
	],
];