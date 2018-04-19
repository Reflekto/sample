<?php


namespace Keypoint\Tools;

use Keypoint\Main\Util as KeypointUtil;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;

/**
 * Утилиты
 *
 * @category    Keypoint
 */
class Util extends KeypointUtil
{
    /**
     * Устанавливает Cookie. Обертка битрикс-метода D7
     *
     * @param string $code код cookie
     * @param string $value значение cookie
     * @param int $time вермя жизни cookie
     */

    public static function setCookie($code, $value, $time = 0)
    {
        $application = Application::getInstance();
        $context = $application->getContext();

        $cookie = new Cookie($code, $value, $time);
        $cookie->setHttpOnly(false);

        $context->getResponse()->addCookie($cookie);
        $context->getResponse()->flush("");


    }

    /**
     * Получает значение Cookie. Обертка битрикс-метода D7
     *
     * @param string $code код cookie
     * @return mixed
     */

    public static function getCookie($code)
    {
        $application = Application::getInstance();
        $context = $application->getContext();

        $value = $context->getRequest()->getCookie($code);
        return $value;
    }

    /**
     * Устанавливает Session
     *
     * @param string $code код для ключа сессии
     * @param string $value значение переменной сессии
     */

    public static function setSession($code, $value)
    {
        $_SESSION[$code] = $value;
    }

    /**
     * Получает значение переменной сессии
     *
     * @param string $code код cookie
     * @return mixed
     */

    public static function getSession($code)
    {
        $value = $_SESSION[$code];
        return $value;
    }

    /**
     * Получение пути от $_SERVER['DOCUMENT_ROOT']
     *
     * @param $link
     * @return string
     */

    public static function getRootPath($link)
    {
        return $_SERVER['DOCUMENT_ROOT'] . $link;
    }

    /**
     * Удаляет из массива arr1 общие элементы с массивом arr2
     *
     * @param array $arr1
     * @param array $arr2
     */
    public static function deleteArraysIntersectElements(&$arr1, $arr2)
    {
        $arIntersect = array_intersect($arr1, $arr2);
        foreach ($arIntersect as $val) {
            $i = array_search($val, $arr1);
            unset($arr1[$i]);
        }
    }

    /**
     * Закрывает доступ для всех кроме админа
     */
    public static function closePublicAccess()
    {
        global $USER;
        if (!$USER->IsAdmin()) {
            echo 'Доступ закрыт!';
            die();
        }
    }

    /**
     * Определяет, что заданная строка заканчивается на заданную подстроку
     *
     * @param string $str Строка
     * @param string $search Подстрока
     *
     * @return bool
     */

    public static function strMatchEnd($str, $search)
    {
        $res = substr($str, strlen($str) - strlen($search)) == $search;
        return $res;
    }

    /**
     * Проверит, что первая строка начинается со второй
     *
     * @param string $str основная строка
     * @param string $substr та, которая может содержаться внутри основной
     *
     * @return bool
     */
    public static function strMatchStart($str, $substr)
    {
        $result = strpos($str, $substr);
        if ($result === 0) { // если содержится, начиная с первого символа
            return true;
        } else {
            return false;
        }
    }

    /**
     * Обрабатывает один уровень дерева меню
     *
     * @param array $parent Родительский пункт меню
     * @param array $items Результат работы компонента bitrix:menu
     * @return void | array
     */
    protected static function menuToTreeLevel(&$parent, &$items)
    {
        while ($items) {
            $item = array_shift($items);
            $item['CHILDREN'] = array();

            if ($item['DEPTH_LEVEL'] > 1 + $parent['DEPTH_LEVEL']) {
                if ($parent['CHILDREN']) {
                    array_unshift($items, $item);
                    self::menuToTreeLevel($parent['CHILDREN'][count($parent['CHILDREN']) - 1], $items);
                }
            } elseif ($item['DEPTH_LEVEL'] < 1 + $parent['DEPTH_LEVEL']) {
                array_unshift($items, $item);
                return;
            } else {
                $parent['CHILDREN'][] = $item;
            }
        }
    }

    /**
     * Преобразует результат работы компонента bitrix:menu в многоуровневое дерево
     *
     * @param array $items Результат работы компонента bitrix:menu
     * @return array
     */
    public static function menuToTree($items)
    {
        $tree = array(
            'TEXT' => '[root]',
            'DEPTH_LEVEL' => 0,
            'CHILDREN' => array(),
        );

        self::menuToTreeLevel($tree, $items);

        return $tree;
    }

    /**
     * Заменяет конструкцию #VAR# на значение из массива.
     * Значения "#SITE_DIR#", "#SITE#", "#SERVER_NAME#" заменяются автоматически из текущих значений.
     *
     * @param string $template Шаблон
     * @param array $data Значения для подстановки
     * @param boolean $fixRepeatableSlashes Убирать продублированные слеши
     * @return string
     */
    public static function parseTemplate($template, $data = array(), $fixRepeatableSlashes = true)
    {
        if ($fixRepeatableSlashes) {
            $template = str_replace('//', '#DOUBLE_SLASH#', $template);
        }

        $string = \CComponentEngine::MakePathFromTemplate($template, $data);

        if ($fixRepeatableSlashes) {
            $string = preg_replace('~[/]{2,}~', '/', $string);
            $string = str_replace('#DOUBLE_SLASH#', '//', $string);
        }

        return $string;
    }

    /**
     * Счетчик обратного отсчета
     *
     * @param mixed $date
     * @param string $template
     * @return string
     */
    public static function downCounter($date, $template = '%s %s %s')
    {
        $checkTime = strtotime($date) - time();
        if ($checkTime <= 0) {
            return false;
        }

        $days = floor($checkTime / 86400);
        $hours = floor(($checkTime % 86400) / 3600);
        $minutes = floor(($checkTime % 3600) / 60);

        $daysStr = self::getNumEnding($days, ['день', 'дня', 'дней']) . ' ';
        $hoursStr = self::getNumEnding($hours, ['час', 'часа', 'часов']) . ' ';
        $minutesStr = self::getNumEnding($minutes, array('минута', 'минуты', 'минут')) . ' ';

        $str = sprintf($template, $daysStr, $hoursStr, $minutesStr);

        return $str;
    }

    /**
     * Возвращает максивальную фложенность массива
     *
     * @param array $array
     * @return int
     */
    public static function getArrayDepth(array $array) {
        $maxDepth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::getArrayDepth($value) + 1;

                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }

        return $maxDepth;
    }

    /**
     * Возвращает признак того, что массив является ассоциативным
     *
     * @param array $arr
     * @return bool
     */
    public static function isAssoc($arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Возвращает признак того, что массив содержит вложенные массивы
     *
     * @param $array
     * @return bool
     */
    public static function isContainsArray($array){
        foreach($array as $value){
            if(is_array($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвращает ключи массива типа int
     *
     * @param $array
     * @return array
     */
    public static function getArrayIntKeys($array)
    {
        $arFilteredKeys = array_filter(
            $array,
            function ($key) {
                return is_int($key);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $arFilteredKeys;
    }

    /**
     * Метод возвращает признак того, что у массива есть ключи int
     *
     * @param array $array
     * @return bool
     */
    public static function isArrayHasIntKeys($array)
    {
        $arFilteredKeys = self::getArrayIntKeys($array);
        return !empty($arFilteredKeys);
    }

    /**
     * Метод возвращает отфарматированную информацию по файлу
     *
     * @param string $filePath
     * @return array
     */
    public static function getFileInfo($filePath)
    {
        $serverRoot = Application::getDocumentRoot();
        $fileSize = filesize($serverRoot.$filePath);
        $info = pathinfo($filePath);

        /**
         * todo:
         * 1.определять расширение файла по MIME типу
         * 2. Добавить поддержку языка в вывод размера
         *
         */
        $extension = mb_strtoupper($info['extension']);
        return [
            'EXTENSION' => $extension,
            'SIZE' => self::getFileSize($fileSize),
            'IF_PDF' => ($extension=='PDF') ? true : false
        ];
    }

    /**
     * Проверяет является ли ссылка на внейший ресурс
     * @param string $link
     * @return bool
     */
    public static function isExternal($link) {
        return (preg_match('#^(http|\/\/)#is', $link) === 1) ? true : false;
    }

    /**
     * Очистка номера телефона от визуального форматирования
     * @param $phone
     * @return string
     */
    public static function clearPhone($phone) {
        /* todo: для гибкости использовать библиотеку обработки телефонных номеров
        /  Пример: https://github.com/giggsey/libphonenumber-for-php
        */
        $phoneResult = preg_replace('#[^0-9]#is', '', $phone);
        $phoneResult = $phoneResult;

        return $phoneResult;
    }

    /**
     * Форматирование номера телефона
     * @param $phone
     * @return string
     */
    public static function beautifyPhone($phone) {
        /* todo: для гибкости использовать библиотеку обработки телефонных номеров
        /  Пример: https://github.com/giggsey/libphonenumber-for-php
        */
        if( preg_match( '/^[\+]{0,1}\d{1}(\d{3})(\d{3})(\d{4})$/', $phone,  $matches) )
        {
            $result = '+7 '.$matches[1] . '-' .$matches[2] . '-' . $matches[3];
            return $result;
        }

        return $phone;
    }

    /**
     * Разворачивание многомерного массива в плоский одномерный массив
     * @param array $multyArray
     * @return array
     */
    public static function flatternArray(array $multyArray) {
        $result = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($multyArray));
        foreach($it as $v) {
            $result[] = $v;
        }
        
        return $result;
    }

    /**
     * Возвращает представление элемента из SVG коллекции
     * @param string $icon_name
     * @return string
     */
    public static function showSvgIcon($icon_name) {
        return '<svg role="img" class="' . $icon_name . '">
            <use xlink:href="#' . $icon_name . '"></use>
        </svg>';
    }
}
