<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class VendorFaq extends CBitrixComponent
{
	public function executeComponent()
	{
		try {
			$this->checkModules();

			// Всегда инициализируем arResult
			$this->arResult = ['ITEMS' => []];

			if ($this->startResultCache()) {
				try {
					$this->getResult();

					// Регистрируем тег ТОЛЬКО для автоматического режима
					if ($this->arParams['CACHE_TYPE'] === 'A') {
						$this->getTaggedCache()->registerTag(
							'iblock_id_' . $this->arParams['IBLOCK_ID']
						);
					}

					$this->includeComponentTemplate();
				} catch (\Throwable $e) {
					$this->abortResultCache();
					throw $e;
				}
			}
		} catch (\Throwable $e) {
			ShowError($e->getMessage());
		}
	}

	protected function checkModules()
	{
		if (!Loader::includeModule('iblock')) {
			throw new SystemException('Модуль iblock не установлен');
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? (int)$arParams['CACHE_TIME'] : 3600;
		$arParams['CACHE_TYPE'] = in_array($arParams['CACHE_TYPE'], ['A', 'Y', 'N']) ? $arParams['CACHE_TYPE'] : 'A';
		$arParams['IBLOCK_ID'] = isset($arParams['IBLOCK_ID']) ? (int)$arParams['IBLOCK_ID'] : 0;
		$arParams['SECTION_ID'] = isset($arParams['SECTION_ID']) ? (int)$arParams['SECTION_ID'] : 0;
		return $arParams;
	}

	protected function getResult()
	{
		if ($this->arParams['IBLOCK_ID'] <= 0) {
			throw new SystemException('Не указан ID инфоблока');
		}

		$filter = [
			'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
		];

		// SECTION_ID может быть 0 — тогда показываем все элементы
		if ($this->arParams['SECTION_ID'] > 0) {
			$filter['IBLOCK_SECTION_ID'] = $this->arParams['SECTION_ID'];
		}

		$result = ElementTable::getList([
			'select' => ['ID', 'NAME', 'PREVIEW_TEXT'],
			'filter' => $filter,
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
		]);

		while ($element = $result->fetch()) {
			$this->arResult['ITEMS'][] = [
				'ID' => (int)$element['ID'],
				'NAME' => (string)$element['NAME'],
				'PREVIEW_TEXT' => $element['PREVIEW_TEXT'],
			];
		}
	}
}