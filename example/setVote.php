<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Simplerating\SimpleRating;

$productId = 23;
$sr = new SimpleRating($productId, __DIR__ . '/ratingDB/');

$rating = rand(1, 10);
$userId = rand(1, 1000);
if (!$sr->isVoted($userId)) {
    $sr->setVote($rating, $userId);
}

echo 'Пользователь поставил: ' . $sr->getRating($userId) . '<br>';
echo 'Средняя оценка: ' . $sr->calc() . '<br>';