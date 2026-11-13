<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Yuiful's site</title>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>
</head>
<body>

<div class="search">

    <label>
        UserName:
        <input type="text" id="userNameInput" placeholder="UserName">
    </label>

    <label>
        RoomID:
        <input type="number" id="idInput" class="no-spin" placeholder="0000">
        <button id="joinRoom">参加</button>
    </label>

    <button id="createRoom">ルームを作成</button>

    <span class="alert"></span>

</div>

<div class="game" style="display: none">

    <h4 class="roomId">RoomID: <span id="roomId"></span></h4>
    <h4 class="userName">UserName: <span id="userName"></span></h4>
    <div class="playerList">
        <h3>Player List</h3>
        <div class="players">
            // <p>
        </div>
    </div>

    <div class="logger">
        Last: <span id="lastWord"></span>Next: <span id="nextLetter"></span>
        <div class="wordInput">
            <input type="text" placeholder="Next word" id="nextWord">
            <button id="wordSubmit">送信</button>
        </div>

        <span id="error"></span>

        <textarea id="gameLog" readonly></textarea>
    </div>


</div>

</body>

<script src="index.js"></script>

</html>