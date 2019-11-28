<?php
require './vendor/autoload.php';
use Dotenv\Dotenv;

// 環境変数読み込み
$dotenv = Dotenv::create(__DIR__);
$dotenv->load();
$accessToken = $_ENV["LINE_KEY"];

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);

//メッセージ取得
$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};

//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

//メッセージ以外のときは何も返さず終了
if ($type != "text") {
    exit;
}

// jsonファイル読み込み
$url = "json/pokemon.json";
$json = file_get_contents($url);
$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$pokemons = json_decode($json, true);

// 検索
$result = "";
foreach ($pokemons as $pokemon) {
    if ($pokemon['name'] === $text) {
        $types = $pokemon['types'];
        break;
    }
}
foreach ($types as $type) {
    $result .= $type;
}

//返信データ作成
$response_format_text2 = "";
$response_format_text3 = "";

$response_format_text = [
    "type" => "text",
    "text" => $result,
];

$post_data = [
    "replyToken" => $replyToken,
    "messages" => [$response_format_text],
];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken,
));
$result = curl_exec($ch);
curl_close($ch);
