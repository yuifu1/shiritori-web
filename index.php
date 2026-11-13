<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Shiritori</title>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
</head>
<body>

<div class="search">

    <label>
        UserName:
        <input type="text" id="userNameInput" placeholder="UserName">
    </label>
    <button id="submitName">名前決定</button>
    <span class="alert"></span>

</div>

<div class="game" style="display: none">

    <h4 class="userName">UserName: <span id="userName"></span></h4>

    <div class="logger">
        Last: <span id="lastWord"></span>Next: <span id="nextLetter"></span>
        <div class="wordInput">
            <input type="text" placeholder="Next word" id="nextWord" autocomplete="new-password">
            <button id="wordSubmit">送信</button>
        </div>

        <span class="alert"></span>

        <textarea id="gameLog" readonly></textarea>
        <p>これまでの単語数: <span id="counter">0</span></p>

    </div>


</div>

</body>

<script src="index.js"></script>

</html>