<?php

namespace Keypoint\Tools\Components;

use Keypoint\Tools\Iblock\Property;
use Keypoint\Tools\Util;
use Keypoint\Tools\User\User;
use Bex\Bbc\Components\ElementsDetail;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Iblock;
use Keypoint\Main\Iblock\Prototype;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Подключаем модуль, т. к. в нём находится класс для базового компонента
if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;
\CBitrixComponent::includeComponentClass('bbc:elements.detail');


class KeypointProductInfoComponent extends ElementsDetail
{
    /**
     * @var object Данные о параметрах страницы
     */

    protected $request;

    /**
     * Опереции до основной логики. Не кешируются
     */
    protected function executeProlog()
    {
        $this->request = Application::getInstance()->getContext()->getRequest();
    }
    
    /**
     * Базовый метод
     */

    protected function executeMain()
    {
        try {
            parent::executeMain();
            
            $this->setCharacteristics($this->arParams['CHARACTERISTIC_CODES']);
            $this->setPhotos();

            $this->arResult['IS_AUTHORIZED'] = User::isAuthorized();
            
            $this->setResultCacheKeys(array(
                "IBLOCK_ID",
                "ID",
                "IBLOCK_SECTION_ID",
                "NAME",
                "PROPERTIES",
                "SECTION",
                "PRODUCT"
            ));

        } catch (\Exception $e) {
            $this->arResult['HAS_ERRORS'] = 'Y';
        }
    }

    /**
     * Операции после основной логики. Не кешируются
     */

    protected function executeEpilog(){
        $this->setBreadcrumbs();
    }

    /**
     * Запись характеристик в результат выборки
     * @param array $props Перечень кодов свойств для вывода Характеристик
     */
    protected function setCharacteristics($props){
        $this->arResult['CHARACTERISTICS'] = array_filter($this->arResult['PROPS'], function($propKey) use ($props) {
            if(in_array($propKey, $props)) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Запись изображений в результат выборки
     */
    protected function setPhotos() {
    
        $arImagePreview = \CFile::ResizeImageGet(
            $this->arResult['DETAIL_PICTURE'],
            array("width" => 165, "height" => 165),
            BX_RESIZE_IMAGE_PROPORTIONAL,
            true,
            false
        );

        $this->arResult['DETAIL_PICTURE_IMAGE'] = [
            'FULL' => \CFile::GetPath($this->arResult['DETAIL_PICTURE']),
            'THUMB' => $arImagePreview['src']
        ];
    }

    /**
     * Установка хлебных крошек разделов
     */
    protected function setBreadcrumbs() {
        global $APPLICATION;
        if($this->arParams["ADD_SECTIONS_PRODUCT_CHAIN"] == 'Y')
        {
            $this->arResult['SECTION']["PATH"] = [];
            $rsPath = \CIBlockSection::GetNavChain(
                $this->arResult['PRODUCT']['IBLOCK_ID'],
                $this->arResult['PRODUCT']['IBLOCK_SECTION_ID'],
                array(
                    "ID", "CODE", "XML_ID", "EXTERNAL_ID", "IBLOCK_ID",
                    "IBLOCK_SECTION_ID", "SORT", "NAME", "ACTIVE",
                    "DEPTH_LEVEL", "SECTION_PAGE_URL"
                )
            );
            $rsPath->SetUrlTemplates("", $this->arParams["SECTION_URL"]);
            while($arPath = $rsPath->GetNext())
            {
                $ipropValues = new Iblock\InheritedProperty\SectionValues($this->arResult['PRODUCT']['IBLOCK_ID'], $arPath["ID"]);
                $arPath["IPROPERTY_VALUES"] = $ipropValues->getValues();
                $this->arResult['SECTION']["PATH"][]= $arPath;
            }

            foreach( $this->arResult['SECTION']["PATH"] as $arPath)
            {
                if ($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != "")
                    $APPLICATION->AddChainItem($arPath["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $arPath["~SECTION_PAGE_URL"]);
                else
                    $APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
            }
        }

        if($this->arParams["ADD_ELEMENT_PRODUCT_CHAIN"] == 'Y')
        {
            $APPLICATION->AddChainItem($this->arResult['NAME']);
        }
    }
    
}
