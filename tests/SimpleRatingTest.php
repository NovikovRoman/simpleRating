<?php
use Simplerating\SimpleRating;

class SimpleRatingTest extends \PHPUnit_Framework_TestCase
{
    private static $path = 'tests/path/';
    /**
     * @var array
     * productId
     */
    private static $arProductId = array(4, 12, 1,);
    /**
     * @var array
     * userId => rating
     */
    private static $arUsersVotes = array(
        1 => 3,
        2 => 1,
        3 => 6,
        4 => 2,
        5 => 3,
        6 => 4,
        7 => 8,
        8 => 9,
        9 => 10,
        10 => 5,
    );

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::rrmdir(self::$path);
    }

    /**
     * @dataProvider votesUsersProvider
     * @param $productId
     * @param $userId
     * @param $score
     * @throws Exception
     */
    public function testVotes($productId, $userId, $score)
    {
        try {
            $sr = new SimpleRating($productId, self::$path);
        } catch (Exception $e) {
            throw $e;
        }
        $sr->setVote($score, $userId);
        $this->assertEquals($sr->isVoted($userId), true);
        $this->assertEquals($sr->getRating($userId), $score);
    }

    /**
     * @dataProvider productProvider
     * @param $productId
     * @throws Exception
     */
    public function testGetId($productId)
    {
        try {
            $sr = new SimpleRating($productId, self::$path);
        } catch (Exception $e) {
            throw $e;
        }
        $this->assertEquals($sr->getID(), $productId);
    }

    /**
     * @dataProvider productProvider
     * @param $productId
     * @throws Exception
     */
    public function testSetId($productId)
    {
        try {
            $sr = new SimpleRating(-1, self::$path);
        } catch (Exception $e) {
            throw $e;
        }
        $sr->setID($productId);
        $this->assertEquals($sr->getID(), $productId);
    }

    public function votesUsersProvider()
    {
        $arResult = array();
        foreach (self::$arProductId as $productId) {
            foreach (self::$arUsersVotes as $userId => $score) {
                $arResult[] = array(
                    $productId,
                    $userId,
                    $score,
                );
            }
        }
        return $arResult;
    }

    /**
     * @dataProvider productProvider
     * @param $productId
     * @throws Exception
     */
    public function testAverage($productId)
    {
        $arAverages = $this->votesUsersProvider();
        $average = 0;
        $count = 0;
        foreach ($arAverages as $item) {
            if ($item[0] !== $productId) {
                continue;
            }
            ++$count;
            $average += $item[2];
        }
        $average /= $count;
        try {
            $sr = new SimpleRating($productId, self::$path);
        } catch (Exception $e) {
            throw $e;
        }
        $this->assertEquals($sr->calc(), $average);
    }

    public function productProvider()
    {
        $arResult = array();
        foreach (self::$arProductId as $productId) {
            $arResult[] = array($productId);
        }
        return $arResult;
    }

    /**
     * @dataProvider productProvider
     * @param $productId
     * @throws Exception
     */
    public function testIsVoted($productId)
    {
        try {
            $sr = new SimpleRating($productId, self::$path);
        } catch (Exception $e) {
            throw $e;
        }
        foreach (self::$arUsersVotes as $userId => $score) {
            $this->assertTrue($sr->isVoted($userId));
        }
    }

    /**
     * @dataProvider productProvider
     * @param $productId
     * @throws Exception
     */
    /*public function testRemoveAll($productId)
    {
        try{
            $sr = new SimpleRating($productId, self::$path);
        } catch (Exception $e) {
            throw $e;
        }
        $sr->removeAll();
        $this->assertTrue(true);
    }*/

    /**
     * @param $path
     * @return bool
     */
    private static function rrmdir($path)
    {
        if (!file_exists($path)) {
            return true;
        }
        if (is_file($path)) {
            return unlink($path);
        }

        $path .= substr($path, -1) === '/' ? '' : '/';
        $objs = scandir($path);
        foreach ($objs as $obj) {
            if ($obj === '.' || $obj === '..') {
                continue;
            }
            $tmpPath = $path . $obj;
            if (is_file($tmpPath)) {
                unlink($tmpPath);
            } else {
                self::rrmdir($tmpPath);
            }
        }
        rmdir($path);
        return true;
    }
}