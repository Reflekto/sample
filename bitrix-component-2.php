<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Keypoint\BarqueCoordinates\Parsers\ParserMarineTraffic;

Loader::registerAutoLoadClasses(null, [
    'Keypoint\BarqueCoordinates\Parsers\ParserMarineTraffic' => '/local/components/keypoint/barque.coordinates/classes/parsers/ParserMarineTraffic.php',
    'Keypoint\BarqueCoordinates\Parser' => '/local/components/keypoint/barque.coordinates/classes/Parser.php',
    'Keypoint\BarqueCoordinates\ParserInterface' => '/local/components/keypoint/barque.coordinates/classes/ParserInterface.php',
]);

Loader::includeModule('highloadblock');

class BarqueCoordinates extends CBitrixComponent
{

    public function onPrepareComponentParams($arParams)
    {
        $arParams["CACHE_TIME"] = !empty($arParams["CACHE_TIME"]) ? $arParams["CACHE_TIME"] : 36000;
        return $arParams;
    }

    public function executeComponent()
    {

        if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->getName(), strtolower(str_replace('\\', '/', __CLASS__)))) {
            $hlblock   = HL\HighloadBlockTable::getList([
                'filter' => ['NAME' => 'Barquecoords']
            ])->fetch();
            $entity   = HL\HighloadBlockTable::compileEntity( $hlblock ); // получим объект - сущность.
            $entityClass = $entity->getDataClass(); // объект класса DataManager

            $class_name = 'Keypoint\BarqueCoordinates\Parsers\\'.$this->arParams['COORDINATES_PARSER'];

            // Создаем объект класса парсера
            /**
             * @var \Keypoint\BarqueCoordinates\ParserInterface $parser;
             */
            $parser = new $class_name();
            $this->arResult['COORDINATES'] = $parser->parseCoordinates();

            // Выберем сохраненное значение из БД, если не удалось получить новые данные
            if(empty($this->arResult['COORDINATES']['LATITUDE'])) {
                $dataOldCoords = $entityClass::getList([
                    'limit' => 1,
                    'order' => ['UF_DATETIME' => 'DESC']
                ])->fetch();

                $this->arResult['COORDINATES']['LATITUDE'] = $dataOldCoords['UF_LATITUDE'];
                $this->arResult['COORDINATES']['LONGITUDE'] = $dataOldCoords['UF_LONGITUDE'];
                $this->arResult['COORDINATES']['DATE'] = $dataOldCoords['UF_DATETIME'];
            } else {
                // Запишем данные
                $entityClass::add(array(
                    'UF_LATITUDE'         => $this->arResult['COORDINATES']['LATITUDE'],
                    'UF_LONGITUDE'         => $this->arResult['COORDINATES']['LONGITUDE'],
                    'UF_DATETIME'         => $this->arResult['COORDINATES']['DATE']
                ));
            }

            if (is_null($this->arResult['COORDINATES'])) {
                $this->abortResultCache();
            }

            $this->includeComponentTemplate();
        }

        return $this->arResult;
    }
}
