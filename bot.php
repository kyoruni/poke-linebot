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

class Pokemon {
    public function __construct(string $name) {
        $this->name                  = $name;
    }

    // ポケモンの基本データを取得
    public function getPokemonData($pokemon) {
        $this->form                  = $pokemon['form'];
        $this->types                 = $pokemon['types'];
        $this->abilities             = $pokemon['abilities'];
        $this->hidden_abilities      = $pokemon['hidden_abilities'];
        $this->status                = $pokemon['status'];
    }

    // カンマ区切り項目を、出力用としてひとつの文字列にする
    public function setOutputText() {
        $this->outputTypes           = implode(",", $this->types);
        $this->outputAbilities       = implode(",", $this->abilities);
        $this->outputHiddenAbilities = implode(",", $this->hidden_abilities);
    }

    // 出力する形に成形
    public function setResult() {
        $line         = "--------------------\n";
        $text         = $line;

        $text        .= "【{$this->name}";

        // フォルムチェンジ or リージョンフォームがあれば、名前の後ろに追記
        if ($this->form) $text .= "({$this->form})";

        $text        .= "】\n";

        $text        .= $line;
        $text        .= "タイプ　：{$this->outputTypes}\n";
        $text        .= "とくせい：{$this->outputAbilities}\n";

        // 夢特性があれば表示
        if ($this->hidden_abilities) $text .= "かくれとくせい：{$this->outputHiddenAbilities}\n";

        $text        .= $line;
        $text        .= "ＨＰ　　：{$this->status['h']}\n";
        $text        .= "こうげき：{$this->status['a']}\n";
        $text        .= "ぼうぎょ：{$this->status['b']}\n";
        $text        .= "とくこう：{$this->status['c']}\n";
        $text        .= "とくぼう：{$this->status['d']}\n";
        $text        .= "すばやさ：{$this->status['s']}\n";
        $this->result = $text;
    }
}

// 結果を返す
function returnPost($text, $replyToken, $accessToken)
{
    $responseFormatText = [
        "type" => "text",
        "text" => $text,
    ];
    $postData = [
        "replyToken" => $replyToken,
        "messages"   => [$responseFormatText],
    ];
    $curl = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt_array($curl, [
        CURLOPT_POST           => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => json_encode($postData),
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/json; charser=UTF-8',
            'Authorization: Bearer ' . $accessToken)
    ]);
    return $curl;
}

// 環境変数読み込み
$dotenv      = Dotenv::create(__DIR__);
$dotenv->load();
$accessToken = $_ENV["LINE_KEY"];

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

// 存在チェック
if (check_exist($input, $pokemons) === false) {
    $return_text = '該当するポケモンが見つかりませんでした…';
    echo $return_text;
    exit;
}

// ポケモンのデータを探す
$resultText = "";
$i           = 1;
foreach ($pokemons as $pokemon) {
    if ($pokemon['name'] === $input) {

        // 名前を取得
        $pokemon_name = $pokemon['name'];

        // ポケモンのオブジェクト作成
        $pokemon_obj    = 'pokemon' . $i;
        ${$pokemon_obj} = new Pokemon($pokemon_name);

        // ポケモンのデータを取得
        ${$pokemon_obj}->getPokemonData($pokemon);

        // 出力する形に成形
        ${$pokemon_obj}->setOutputText();
        ${$pokemon_obj}->setResult();

        // 結果を返す
        $resultText .= ${$pokemon}->result;
        $i ++;
    }
}
$curl   = return_post($resultText, $replyToken, $accessToken);
$result = curl_exec($curl);
curl_close($curl);