<?php
require './vendor/autoload.php';
use Dotenv\Dotenv;

// データ読み込み
function load_json($url)
{
    $json = file_get_contents($url);
    $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
    return json_decode($json, true);
}

// 存在チェック
function check_exist($search, $items, $column)
{
    return in_array($search, array_column($items, $column));
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
// $type  = $jsonObj->{"events"}[0]->{"message"}->{"type"};
// $input = $jsonObj->{"events"}[0]->{"message"}->{"text"};

// //ReplyToken取得
// $replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

// //メッセージ以外のときは何も返さず終了
// if ($type != "text") {
//   exit;
// }

// jsonファイル読み込み
// データ読み込み
$pokemons = load_json("json/pokemon.json");
$types    = load_json("json/pokemon_type.json");

$input = "ピカチュウ、ギャラドス";

if (strpos($input,"、") !== false) {
    // ユーザーからのメッセージに、「、」が含まれていたら二匹
    $check_pokemons = explode("、", $input);
} else {
    // ユーザーからのメッセージに、「、」が含まれていなかったら1匹
    $check_pokemons = array($input);
}

// 存在チェック
foreach ($check_pokemons as $check_pokemon) {
    if (check_exist($check_pokemon, $pokemons, 'name') == false) {
      $text = "該当するポケモンが見つかりません…";
      exit;
      // $curl   = return_post($text, $replyToken, $accessToken);
      // $result = curl_exec($curl);
      // curl_close($curl);
    }
}