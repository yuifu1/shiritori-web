<?php
header('Content-Type: application/json');
session_start();

$db_file = __DIR__ . "/shiritori.db";

try {
    $pdo = new PDO('sqlite:' . $db_file);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB接続失敗']);
    exit;
}
$pdo->exec("CREATE TABLE IF NOT EXISTS words (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    author TEXT NOT NULL,
    word TEXT NOT NULL,
    yomi TEXT,
    last_letter TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");

$action = $_GET['action'] ?? '';

if($action === 'login'){
    if(!empty($_SESSION['username'])){
        echo json_encode(['success' => true, 'username' => $_SESSION['username']]);
        exit;
    }
    $username = $_POST['username'] ?? '';
    if(empty($username)){
        echo json_encode(['success' => false, 'error' => '名前を入力してください。' . json_encode($_POST)]);
        exit;
    }
    $_SESSION['username'] = $username;
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    echo json_encode(['success' => true, 'username' => $_SESSION['username']]);
    exit;
}

if($action === 'logout3'){ // 秘匿
    unset($_SESSION['username']);
    echo json_encode(['success' => true]);
    exit;
}

$username = $_SESSION['username'] ?? '';

if($action === 'fetch') {
    $limit = $_GET['limit'] ?? 0;
    $history = $pdo->query("SELECT * FROM words ORDER BY created_at")->fetchAll(); // 後ろから10件取得し、元の順に
    echo json_encode([
        'logged_in' => !empty($username),
        'username' => $username,
        'history' => array_slice($history, -$limit),
        'count' => count($history)
    ]);
    exit;
}

const CLIENT_ID = 'dmVyPTIwMjUwNyZpZD1iMXlvM09pMXF4Jmhhc2g9TURVNVl6bGlaR1kwTVRRNVpUWTFZdw';
const REQUEST_URL = 'https://jlp.yahooapis.jp/jsonrpc';
function toHiragana(string $w): string { // ひらがなに変換
    $w = trim($w);
    $w = strip_tags($w);
	$w = mb_convert_kana($w, "aHc");
	$res = file_get_contents(REQUEST_URL, false, stream_context_create([
		'http' => [
			'method' => 'POST',
			'header' => [
				"Content-Type: application/json",
				"User-Agent: Yahoo AppID: " . CLIENT_ID
			],
			'content' => json_encode([
				'id' => '1234-1',
				'jsonrpc' => '2.0',
				'method' => 'jlp.furiganaservice.furigana',
				'params' => [
					'q' => $w,
					'grade' => 1
				]
			])
		]
	]));
	$res = json_decode($res, true);
	if(isset($res['result']['word'])) {
		$w = '';
		foreach($res['result']['word'] as $word)
			$w .= $word['furigana'] ?? $word['surface'];
	}
    return $w;
}
function hiragataLarge($str) {
	$small = ['ぁ', 'ぃ', 'ぅ', 'ぇ', 'ぉ', 'っ', 'ゃ', 'ゅ', 'ょ', 'ゎ'];
	$large = ['あ', 'い', 'う', 'え', 'お', 'つ', 'や', 'ゆ', 'よ', 'わ'];
	return str_replace($small, $large, $str);
}

if($action === 'word') {

	$username = $_SESSION['username'] ?? '';
	if(empty($username)){
		echo json_encode(['success' => false, 'error' => 'Please login']);
		exit;
	}

    $word = $_POST['word'] ?? '';
    $word = trim($word);
	if(empty($word)){
        echo json_encode(['success' => false, 'error' => 'Please enter word']);
        exit;
    }
	$isRegistered = $pdo->query("SELECT word FROM registered_words WHERE word = '{$word}'")->fetch();
	if($isRegistered === false){
		echo json_encode(['success' => false, 'error' => 'Not registered word']);
		exit;
	}
    $exist = $pdo->query("SELECT word FROM words WHERE word = '{$word}';")->fetch();
    if($exist !== false){
        echo json_encode(['success' => false, 'error' => 'Used word']);
        exit;
    }
	$hiragana = toHiragana($word);
    $head_letter = mb_substr($hiragana, 0, 1);
	$last_letter = mb_substr($hiragana, -1, 1);
	$last_letter = hiragataLarge($last_letter);
	if($last_letter === 'ー') $last_letter = mb_substr($hiragana, -2, 1);
    $last = $pdo->query("SELECT last_letter FROM words ORDER BY words.created_at DESC LIMIT 1;")->fetch();
    if($last !== false) {
        if ($head_letter !== $last[0] && $last[0] !== '') {
            echo json_encode(['success' => false, 'error' => 'Wrong first letter to use']);
            exit;
        }
    }
    $pdo->query("INSERT INTO words (word, author, yomi, last_letter) VALUES ('{$word}', '{$username}', '{$hiragana}', '{$last_letter}');");
    echo json_encode(['success' => true, 'hiragana' => $hiragana]);
    exit;
}

const ADMIN_NAME = 'ADMINISTRATOR';
if($action === 'reset') {
	$username = $_SESSION['username'] ?? '';
	if(empty($username)){
		echo json_encode(['success' => false, 'error' => 'Please login']);
		exit;
	}
	if($username !== ADMIN_NAME){
		echo json_encode(['success' => false, 'error' => 'Permission denied']);
		exit;
	}
	$pdo->query("DELETE FROM words;");
	echo json_encode(['success' => true]);
	exit;
}