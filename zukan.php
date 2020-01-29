<?php
require './vendor/autoload.php';
use Dotenv\Dotenv;

// データ読み込み
function load_json($url) {
    $json = file_get_contents($url);
    $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
    return json_decode($json, true);
}

// 存在チェック
function check_exist($input, $pokemons) {
    return in_array($input, array_column($pokemons, 'name'));
}

// // 環境変数読み込み
// $dotenv = Dotenv::create(__DIR__);
// $dotenv->load();
// $accessToken = $_ENV["LINE_KEY"];

// //ユーザーからのメッセージ取得
// $json_string = file_get_contents('php://input');
// $jsonObj     = json_decode($json_string);

// //ユーザーからのメッセージ取得
// $json_string = file_get_contents('php://input');
// $jsonObj     = json_decode($json_string);

// //メッセージ取得
// $type        = $jsonObj->{"events"}[0]->{"message"}->{"type"};
// $input       = $jsonObj->{"events"}[0]->{"message"}->{"text"};

// //ReplyToken取得
// $replyToken  = $jsonObj->{"events"}[0]->{"replyToken"};

// //メッセージ以外のときは何も返さず終了
// if ($type != "text") {
//     exit;
// }

// jsonファイル読み込み
$pokemons = load_json("json/pokemon.json");

$input = 'ピカチュウ';

if (check_exist($input, $pokemons)) {
  echo 'いるよ';
} else {
  echo 'いないよ';
}