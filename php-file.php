<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 04.04.2018
 * Time: 15:36
 */

namespace Keypoint\Tools\File;
use Bitrix\Main\Application;
use Keypoint\Tools\User\User;

class File implements FileInterface
{
    private $resource;
    private $path;
    private $fileName;

    public function __construct($params)
    {
        $this->path = $params['tmp_name'];
        $this->fileName = $params['name'];
    }

    public function open()
    {
        $this->resource = fopen($this->getPath(), 'r');
    }

    /**
     * Запись файла
     * @param string $path
     */
    public function save($path)
    {
        $fileToWrite = fopen($path, 'w');
        while (($line = fgets($this->resource)) !== false) {
            fputs($fileToWrite, $line);
        }

        fclose($fileToWrite);
    }

    /**
     * Удалить файл
     */
    public function delete() {
        
        unlink($this->getPath());

        // Удаление пустой директории
        $cntFiles = 0;
        foreach (new \DirectoryIterator(dirname($this->getPath())) as $fileInfo) {
            if(!$fileInfo->isDot()) {
                $cntFiles++;
            }
        }
        
        if($cntFiles === 0) {
            rmdir(dirname($this->getPath()));
        }
    }

    /**
     * Запись во временный файл
     */
    public function tempSave()
    {
       $tempPath = self::getTempPath();

        if(!is_dir($tempPath)) {
            mkdir($tempPath);
        }

        // Очистка директории
        foreach (new \DirectoryIterator($tempPath) as $fileInfo) {
            if(!$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }

        $this->save($tempPath.'/'.$this->fileName);
    }

    /**
     * Возвращает путь к временной папке пользователя
     * @return string
     */
    private function getTempPath() {
        $server = Application::getInstance()->getContext()->getServer();
        // @todo: вынести путь временных файлов в конфиги
        $tmpPath = $server->getDocumentRoot().'/upload/tmp/users/';
        $sessionDir = $tmpPath.session_id();

        return $sessionDir;
    }


    /**
     * Возвращает экземпляр класса для временного файла пользователя
     * @return File
     */
    public static function getTemp() {
        $tempPath = self::getTempPath();
        $params = [];
        foreach(new \DirectoryIterator($tempPath) as $fileInfo) {
            if(!$fileInfo->isDot()) {
                $params['tmp_name'] = $fileInfo->getPathname();
                $params['name'] = $fileInfo->getFilename();

                return new static($params);
            }
        }

        return null;
    }

    /**
     * Регистрация файла в системе Битрикс и привязка его к пользовательскому полю
     * @param $userId
     * @param $propCode
     * @return bool|int|string
     */
    public function attachToUserProperty($userId, $propCode)
    {
        $arFile = \CFile::MakeFileArray($this->getPath());
        $arFile["MODULE_ID"] = "main";

        $res = User::updateUser($userId, [
            $propCode => $arFile
        ]);

        return $res;
    }

    /**
     * Возвращает исходное название файла
     * @return string
     */
    public function getName()
    {
        return $this->fileName;
    }
    
    public function getPath() {
        return $this->path;
    }

    /**
     * Вовзаращает размер файла в байтах
     * @return int
     */
    public function getSize()
    {
        return filesize($this->getPath());
    }

    /**
     * Возвращаем MIME тип файла
     * @return string
     */
    public function getMimeType()
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $this->getPath());
    }
}
