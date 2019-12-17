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

// ポケモンのタイプと特性を取得
function get_types_and_abilitys($pokemons, $search)
{
    foreach ($pokemons as $pokemon) {
        if ($pokemon['name'] === $search) {
            $pokemon_types           = $pokemon['types'];
            $pokemon_abilitys        = $pokemon['abilitys'];
            $pokemon_hidden_abilitys = $pokemon['hidden_abilitys'];
            break;
        }
    }
    return array($pokemon_types, $pokemon_abilitys, $pokemon_hidden_abilitys);
}

// 配列を、カンマ区切りのテキストに整形
function trim_array_to_text($text_array)
{
    $result = "";
    foreach ($text_array as $text) {
        $result .= $text . ",";
    }
    return rtrim($result, ',');
}

// ポケモンの弱点、いまひとつ、無効タイプを取得
function get_weakness_and_resistance($pokemon_type, $types)
{
    foreach ($types as $type) {
        if ($pokemon_type == $type['name']) {
            $weak_types    = $type['weaks'];
            $resist_types  = $type['resists'];
            $invalid_types = $type['invalids'];
        }
    }
    return array($weak_types, $resist_types, $invalid_types);
}

// 弱点、いまひとつ、無効の計算
function calc_damage($mode, $type, $search_types, $damage)
{
    // $type：検索対象のタイプ
    // $search_types：ポケモンの持っている弱点、いまひとつ、無効のタイプ
    foreach ($search_types as $search_type) {
        if ($type == $search_type) {
            if ($mode == 'weak') {
                // 弱点の場合、ダメージ倍率+1
                $damage *= 2;
            } elseif ($mode == 'resist') {
                // いまひとつの場合、ダメージ倍率*0.5
                $damage *= 0.5;
            } elseif ($mode == 'invalid') {
                // 無効の場合、ダメージ倍率は0
                $damage = 0;
            }
        }
    }
    return $damage;
}

// 出力用タイプの整形
function trim_display_types($result_types)
{
    $weak4          = "";
    $weak2          = "";
    $resist         = "";
    $resist_quarter = "";
    $invalid        = "";
    foreach ($result_types as $result_type) {
        if ($result_type['damage'] == 4) {
            $weak4   .= "・" . $result_type['name'] . "\n";
        } elseif ($result_type['damage'] == 2) {
            $weak2   .= "・" . $result_type['name'] . "\n";
        } elseif ($result_type['damage'] == 0.5) {
            $resist  .= "・" . $result_type['name'] . "\n";
        } elseif ($result_type['damage'] == 0.25) {
            $resist_quarter .= "・" . $result_type['name'] . "\n";
        } elseif ($result_type['damage'] == 0) {
            $invalid .= "・" . $result_type['name'] . "\n";
        }
    }
    return array($weak4, $weak2, $resist, $resist_quarter, $invalid);
}

// 区切り線を表示
function echo_line()
{
    return "----------------\n";
}

// 特性：備考欄追記チェック
function check_ability($ability)
{
    $result = "";
    $comment_flg = false; // 備考欄追記があればtrue
    $patterns = array();
    $patterns[] = array("name" => "アロマベール", "text" => "状態変化無効");
    $patterns[] = array("name" => "かんそうはだ", "text" => "みずタイプの技無効");
    $patterns[] = array("name" => "きゅうばん", "text" => "強制交代無効(ほえる等)");
    $patterns[] = array("name" => "クリアボディ", "text" => "自分以外から受ける能力ダウン無効");
    $patterns[] = array("name" => "メタルプロテクト", "text" => "自分以外から受ける能力ダウン無効");
    $patterns[] = array("name" => "しめりけ", "text" => "全員の爆発技が失敗する、ゆうばくでダメージを受けない");
    $patterns[] = array("name" => "じゅうなん", "text" => "まひ無効");
    $patterns[] = array("name" => "じょおうのいげん", "text" => "自分と味方への先制技無効");
    $patterns[] = array("name" => "ビビットボディ", "text" => "自分と味方への先制技無効");
    $patterns[] = array("name" => "しろいけむり", "text" => "自分以外から受けるぼうぎょダウン無効");
    $patterns[] = array("name" => "はとむね", "text" => "自分以外から受けるぼうぎょダウン無効");
    $patterns[] = array("name" => "スイートベール", "text" => "自分と味方へのねむり、あくび無効");
    $patterns[] = array("name" => "ふみん", "text" => "自分と味方へのねむり、あくび無効");
    $patterns[] = array("name" => "するどいめ", "text" => "命中率が下がらない、回避率を無視して攻撃");
    $patterns[] = array("name" => "そうしょく", "text" => "くさタイプの技無効");
    $patterns[] = array("name" => "ちくでん", "text" => "でんきタイプの技無効");
    $patterns[] = array("name" => "ちょすい", "text" => "みずタイプの技無効");
    $patterns[] = array("name" => "よびみず", "text" => "みずタイプの技無効、みずタイプの技を引き寄せる");
    $patterns[] = array("name" => "もらいび", "text" => "ほのおタイプの技無効");
    $patterns[] = array("name" => "テレパシー", "text" => "味方からの攻撃を受けない");
    $patterns[] = array("name" => "でんきエンジン", "text" => "でんきタイプの技とでんじは無効");
    $patterns[] = array("name" => "どんかん", "text" => "メロメロ ゆうわく ちょうはつ いかくの効果を受けない");
    $patterns[] = array("name" => "ねんちゃく", "text" => "もちものを奪われない");
    $patterns[] = array("name" => "ばけのかわ", "text" => "フォルムチェンジ前の状態で攻撃を受けた場合、1/8のダメージを受けてフォルムチェンジする");
    $patterns[] = array("name" => "パステルベール", "text" => "自分と味方へのどく状態無効");
    $patterns[] = array("name" => "ひらいしん", "text" => "でんきタイプの技無効");
    $patterns[] = array("name" => "ふしぎなまもり", "text" => "攻撃技のダメージが、こうかばつぐん以外無効");
    $patterns[] = array("name" => "ふゆう", "text" => "じめんタイプの技、まきびし、どくびし、ねばねばネット、ありじごく、たがやす、フィールドの効果無効");
    $patterns[] = array("name" => "フラワーベール", "text" => "自分と味方のくさタイプのポケモンは能力が下がらなくなり、状態異常とねむけ無効");
    $patterns[] = array("name" => "ぼうおん", "text" => "音技無効");
    $patterns[] = array("name" => "ぼうじん", "text" => "あられ、すなあらし、粉系の技、ほうし無効");
    $patterns[] = array("name" => "ぼうだん", "text" => "ボール 砲 弾 爆弾系 くちばしキャノン ロックブラスト かふんだんご無効");
    $patterns[] = array("name" => "マイペース", "text" => "こんらん、いかく無効");
    $patterns[] = array("name" => "みずのベール", "text" => "やけど無効");
    $patterns[] = array("name" => "めんえき", "text" => "どく無効");
    $patterns[] = array("name" => "やるき", "text" => "ねむり、ねむけ無効");
    foreach ($patterns as $pattern) {
        $match = $pattern["name"];
        if (preg_match("/$match/", $ability) === 1) {
            $result .= "・" . $pattern["name"] . "：" . $pattern["text"] . "\n";
            if ($comment_flg === false) {
                $comment_flg = true;
            }
        }
    }
    return $result;
}

// 結果を整形して返す
function trim_result($pokemon)
{
    // 名前
    $name = $pokemon['name'] . "\n" . echo_line();

    // ポケモンのタイプ
    $type = "【タイプ】\n" . $pokemon['type'] . "\n" . echo_line();

    // 特性
    $ability = "【とくせい】\n" . $pokemon['ability'] . "\n" . echo_line();

    // 夢特性
    $hidden_ability = "";
    if (!empty($pokemon['hidden_ability'])) {
        $hidden_ability = "【かくれとくせい】\n" . $pokemon['hidden_ability'] . "\n" . echo_line();
    }

    // 4倍弱点
    $weak4 = "";
    if (!empty($pokemon['weak4'])) {
        $weak4 = "【効果ばつぐん×4】\n" . $pokemon['weak4'] . echo_line();
    }

    // 2倍弱点
    $weak2 = "";
    if (!empty($pokemon['weak2'])) {
        $weak2 = "【効果ばつぐん×2】\n" . $pokemon['weak2'] . echo_line();
    }

    // いまひとつ
    $resist = "";
    if (!empty($pokemon['resist'])) {
        $resist = "【効果いまひとつ×0.5】\n" . $pokemon['resist'] . echo_line();
    }

    // いまひとつ 0.25
    $resist_quarter = "";
    if (!empty($pokemon['resist_quarter'])) {
        $resist_quarter = "【効果いまひとつ×0.25】\n" . $pokemon['resist_quarter'] . echo_line();
    }

    // 効果がない
    $invalid = "";
    if (!empty($pokemon['invalid'])) {
        $invalid = "【効果がない】\n" . $pokemon['invalid'] . echo_line();
    }

    // 備考欄
    $comment = "";
    $wk_comment = check_ability($pokemon['ability'] . ", " . $pokemon['hidden_ability']);
    if (!empty($wk_comment)) {
        $comment = "【備考】\n" . $wk_comment;
    }
    return $name . $type . $ability . $hidden_ability . $weak4 . $weak2 . $resist . $resist_quarter . $invalid . $comment;
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

//メッセージ取得
$type  = $jsonObj->{"events"}[0]->{"message"}->{"type"};
$input = $jsonObj->{"events"}[0]->{"message"}->{"text"};

//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

//メッセージ以外のときは何も返さず終了
if ($type != "text") {
    exit;
}

// jsonファイル読み込み
// データ読み込み
$pokemons = load_json("json/pokemon.json");
$types    = load_json("json/pokemon_type.json");

// 存在チェック
if (check_exist($input, $pokemons, 'name') == false) {
    $text   = "該当するポケモンが見つかりません…";
    $curl   = return_post($text, $replyToken, $accessToken);
    $result = curl_exec($curl);
    curl_close($curl);
}

// ポケモンの名前、タイプと特性を取得
$pokemon         = array();
$pokemon['name'] = $input;
list($pokemon_types, $pokemon_abilitys, $pokemon_hidden_abilitys) = get_types_and_abilitys($pokemons, $input);

// ポケモンのタイプ、特性を整形
$pokemon['type']           = trim_array_to_text($pokemon_types);
$pokemon['ability']        = trim_array_to_text($pokemon_abilitys);
$pokemon['hidden_ability'] = trim_array_to_text($pokemon_hidden_abilitys);

// ポケモンの弱点、いまひとつ、無効タイプを取得
$pokemon_weak_types    = array();
$pokemon_resist_types  = array();
$pokemon_invalid_types = array();
foreach ($pokemon_types as $pokemon_type) {
    list($weak_types, $resist_types, $invalid_types) = get_weakness_and_resistance($pokemon_type, $types);
    foreach ($weak_types as $weak_type) {
        array_push($pokemon_weak_types, $weak_type);
    }
    foreach ($resist_types as $resist_type) {
        array_push($pokemon_resist_types, $resist_type);
    }
    foreach ($invalid_types as $invalid_type) {
        array_push($pokemon_invalid_types, $invalid_type);
    }
}

// 弱点、いまひとつ、無効の計算
$result_types = array();
foreach ($types as $type) {
    $damage         = 1;
    $damage         = calc_damage('weak', $type['name'], $pokemon_weak_types, $damage);
    $damage         = calc_damage('resist', $type['name'], $pokemon_resist_types, $damage);
    $damage         = calc_damage('invalid', $type['name'], $pokemon_invalid_types, $damage);
    $result_types[] = array('name' => $type['name'], 'damage' => $damage);
}

// 出力用タイプの整形
list($pokemon['weak4'], $pokemon['weak2'], $pokemon['resist'], $pokemon['resist_quarter'], $pokemon['invalid']) = trim_display_types($result_types);

// 結果を返す
$text   = trim_result($pokemon);
$curl   = return_post($text, $replyToken, $accessToken);
$result = curl_exec($curl);
curl_close($curl);
