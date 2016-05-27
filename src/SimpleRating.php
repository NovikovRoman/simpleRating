<?php
/**
 * Class SimpleRating
 * класс для ведения учета рейтинга объектов (товаров, фото и тп)
 * Пользователи учитываются по IP
 *
 */

namespace Simplerating;

use \Exception;

class SimpleRating
{
    private $folder_rating = '';
    private $id = '';
    private $folder_for_file = '';
    private $path_to_file = '';

    /**
     * SimpleRating constructor.
     *
     * @param $id (int|string) - id объекта для которого установим оценку
     * @param $folder_rating - путь для хранения рейтингов
     * @throws Exception
     */
    public function __construct($id, $folder_rating)
    {
        $this->folder_rating = $folder_rating;
        if (!@mkdir($this->folder_rating, 0700, true) && !is_dir($this->folder_rating)) {
            $e = new Exception('method getFilename: Can not create directory ' . $this->folder_for_file);
            throw $e;
        }
        $this->folder_rating .= substr($this->folder_rating, -1) === '/' ? '' : '/';
        $this->setID($id);
    }

    /**Установить новый id
     *
     * @param (int|string) $id - id объекта
     * @return $this
     * @throws \Exception
     */
    public function setID($id)
    {
        $this->id = $id;
        $this->getFilename();
        return $this;
    }

    /**Получить id объекта
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**Оценивал ли пользователь объект
     *
     * @param string $userId - id пользователя. По-умолчанию определяется IP
     * @return bool
     */
    public function isVoted($userId = '')
    {
        $userId = $this->canonicalUserId($userId);
        // проверим голосовал ли за эту запись?
        if (!file_exists($this->path_to_file)) {
            return false;
        }
        $data = file_get_contents($this->path_to_file);
        return (bool)preg_match('/^(' . preg_quote($userId) . '=.+?$)/mu', $data, $m);
    }

    /**Получить среднюю оценку пользователя за объект
     *
     * @param string $userId - id пользователя. По-умолчанию определяется IP
     * @return float
     */
    public function getRating($userId = '')
    {
        $userId = $this->canonicalUserId($userId);
        $rating = 0.0;
        // проверим голосовал ли за эту запись?
        if (!file_exists($this->path_to_file)) {
            return $rating;
        }
        $data = file_get_contents($this->path_to_file);
        preg_match_all('/^' . preg_quote($userId) . '=(.+?)$/mu', $data, $m);
        if (count($m[1])) {
            $rating = array_sum($m[1]) / count($m[1]);
        }
        return $rating;
    }

    /**Поставить оценку объекту
     *
     * @param float  $rating - число
     * @param string $userId - id пользователя. По-умолчанию определяется IP
     * @return $this
     */
    public function setVote($rating = 0.0, $userId = '')
    {
        $rating = (float)$rating;
        $userId = $this->canonicalUserId($userId);
        $entity = $userId . '=' . $rating . "\n";
        $fh = fopen($this->path_to_file, 'a+');
        fwrite($fh, $entity);
        fclose($fh);
        return $this;
    }

    /**Удалить все оценки пользователя за объект
     *
     * @param string $userId - id пользователя. По-умолчанию определяется IP
     * @return $this
     */
    public function removeVote($userId = '')
    {
        $userId = $this->canonicalUserId($userId);
        $data = file_get_contents($this->path_to_file);
        $data = preg_replace('/^(' . preg_quote($userId) . '=.+?$)/mu', '', $data);
        $fh = fopen($this->path_to_file, 'w+');
        fwrite($fh, $data);
        fclose($fh);
        return $this;
    }

    /**Рассчитать среднюю оценку за объект
     *
     * @return float
     */
    public function calc()
    {
        if (file_exists($this->path_to_file)) {
            $data = file_get_contents($this->path_to_file);
            preg_match_all('/.+?=(.+?)\n/', $data, $matches);
            if (count($matches[1])) {
                return array_sum($matches[1]) / count($matches[1]);
            }
        }
        return 0.0;
    }

    /**Удалить информацию о продукте
     *
     * @return $this
     */
    public function removeAll()
    {
        if (file_exists($this->path_to_file)) {
            unlink($this->path_to_file);
            $files = scandir($this->folder_for_file);
            if (count($files) === 2) {
                rmdir($this->folder_for_file);
            }
        }
        return $this;
    }

    /**Сгенерировать имя файла для хранения оценок
     *
     * @return $this
     * @throws Exception
     */
    private function getFilename()
    {
        $this->folder_for_file = $this->folder_rating . substr(md5($this->id), 0, 3) . '/';
        if (!@mkdir($this->folder_for_file, 0700, true) && !is_dir($this->folder_for_file)) {
            $e = new Exception('method getFilename: Can not create directory ' . $this->folder_for_file);
            throw $e;
        }
        $this->path_to_file = $this->folder_for_file . $this->id;
        return $this;
    }

    /**Приводим id пользователя к определенному виду. По-умолчанию определяется IP
     *
     * @param $userId (int|string)
     * @return string
     */
    private function canonicalUserId($userId)
    {
        return $userId ?: $this->getIP();
    }

    /**Получить ip пользователя (xxx.xxx.xxx.xxx)
     *
     * @return string
     */
    private function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}