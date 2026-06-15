<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
// Классы для работы с инфоблоком
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
// Классы для кеширования
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
// Класс для системных исключений
use Bitrix\Main\SystemException;
class VendorFaq extends CBitrixComponent
{
	public function executeComponent()
	{
		try {
			$this->checkModules();
			$this->getResult();
			$this->includeComponentTemplate();
		} catch (SystemException $e) {
			ShowError($e->getMessage());
		}
	}

	protected function checkModules()
	{
		if (!Loader::includeModule('iblock')) {
			throw new SystemException('Модуль iblock не установлен');
		}
	}

	// обработка массива $arParams (метод подключается автоматически)
	public function onPrepareComponentParams($arParams)
	{
		// Время кеша
		$arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ? (int)$arParams['CACHE_TIME'] : 3600;
		if (!in_array($arParams['CACHE_TYPE'], ['A', 'Y', 'N'])) {
			$arParams['CACHE_TYPE'] = 'A';
		}
		return $arParams;
	}

	protected function getResult()
	{
		$cacheType = $this->arParams['CACHE_TYPE'];
		$cacheTime = $this->arParams['CACHE_TIME'];
		$cacheId = md5(serialize($this->arParams));
		$cachePath = '/vendor_faq/';


		$cache = Cache::createInstance();
		$taggedCache = Application::getInstance()->getTaggedCache();

		// Проверяем кеш (только если не N)
		if ($cacheType !== 'N' && $cache->initCache($cacheTime, $cacheId, $cachePath)) {
			// Кеш есть — берём и выходим
			$this->arResult = $cache->getVars();
			return;
		}

		// Открываем запись кеша (только если не N)
		if ($cacheType !== 'N') {
			$cache->startDataCache();
		}

		// Для авто регистрируем тег инфоблока
		if ($cacheType === 'A') {
			$taggedCache->startTagCache($cachePath);
			$taggedCache->registerTag('iblock_id_' . $this->arParams['IBLOCK_ID']);
		}

		// Получаем данные
		$result = ElementTable::getList([
			'select' => ['ID', 'NAME', 'PREVIEW_TEXT'],
			'filter' => [
				'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
				'IBLOCK_SECTION_ID' => $this->arParams['SECTION_ID'],
				'ACTIVE' => 'Y',
			],
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
		]);

		while ($element = $result->fetch()) {
			$this->arResult['ITEMS'][] = $element;
		}

		// Закрываем и сохраняем кеш
		if ($cacheType === 'A') {
			$taggedCache->endTagCache();
		}
		if ($cacheType !== 'N') {
			$cache->endDataCache($this->arResult);
		}

	}
}







?>