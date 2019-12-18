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
function check_exist($input, $pokemons)
{
    return in_array($input, array_column($pokemons, 'name'));
}

class Pokemon
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    // タイプと種族値を取得
    public function get_types_and_status($pokemons) {
        foreach($pokemons as $pokemon) {
            if ($this->name === $pokemon['name']) {
                $this->types  = $pokemon['types'];
                $this->status = $pokemon['status'];
            }
        }
    }

    // タイプをカンマ区切りの文字列にする
    public function trim_types() {
        $this->types = implode(",", $this->types);
    }

    // 出力する形に整形
    public function set_result() {
        $text  = "--------------------\n";
        $text .= "【{$this->name}】";
        $text .= $this->types . PHP_EOL;
        $text .= "H[{$this->status['h']}] ";
        $text .= "A[{$this->status['a']}] ";
        $text .= "B[{$this->status['b']}] ";
        $text .= "C[{$this->status['c']}] ";
        $text .= "D[{$this->status['d']}] ";
        $text .= "S[{$this->status['s']}] " . PHP_EOL;
        $this->result = $text;
    }
}

// 結果を返す
function return_post($text, $replyToken, $accessToken)
{
    $response_format_text = [
        "type" => "text",
        "text" => $text,
    ];
    $post_data = [
        "replyToken" => $replyToken,
        "messages"   => [$response_format_text],
    ];
    $curl = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt_array($curl, [
        CURLOPT_POST           => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => json_encode($post_data),
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/json; charser=UTF-8',
            'Authorization: Bearer ' . $accessToken)
    ]);
    return $curl;
}

// 環境変数読み込み
$dotenv = Dotenv::create(__DIR__);
$dotenv->load();
$accessToken = $_ENV["LINE_KEY"];

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj     = json_decode($json_string);

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj     = json_decode($json_string);

//メッセージ取得
$type        = $jsonObj->{"events"}[0]->{"message"}->{"type"};
$input       = $jsonObj->{"events"}[0]->{"message"}->{"text"};

//ReplyToken取得
$replyToken  = $jsonObj->{"events"}[0]->{"replyToken"};

//メッセージ以外のときは何も返さず終了
if ($type != "text") {
    exit;
}

// jsonファイル読み込み
$pokemons = load_json("json/pokemon.json");

if (strpos($input,"、") !== false) {
    // ユーザーからのメッセージに、「、」が含まれていたら複数
    $check_pokemons = explode("、", $input);
} else {
    // ユーザーからのメッセージに、「、」が含まれていなかったら1匹
    $check_pokemons = array($input);
}

// 存在チェック
foreach ($check_pokemons as $check_pokemon) {
    if (check_exist($check_pokemon, $pokemons) === false) {
        $text = "該当するポケモンが見つかりません…";
        $curl   = return_post($text, $replyToken, $accessToken);
        $result = curl_exec($curl);
        curl_close($curl);
        exit;
    }
}

$i = 1;
$result_text = "";
foreach ($check_pokemons as $check_pokemon) {
    $pokemon    = 'pokemon' . $i;
    ${$pokemon} = new Pokemon($check_pokemon);

    // タイプと種族値を取得
    ${$pokemon}->get_types_and_status($pokemons);
    ${$pokemon}->trim_types();

    // 出力する形に整形
    ${$pokemon}->set_result();

    // 結果を返す
    $result_text .= ${$pokemon}->result;
    $i ++;
}
$curl   = return_post($result_text, $replyToken, $accessToken);
$result = curl_exec($curl);
curl_close($curl);