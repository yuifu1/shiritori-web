const alert = $(".alert");
const search = $(".search");
const game = $(".game");
const textarea = $("#gameLog");

function log(s) {
    textarea.text(textarea.text().slice(-10000) + `\n${s}`); // 最大10000+字
    textarea.scrollTop(textarea[0].scrollHeight - textarea[0].clientHeight);
}

async function reFetch() {
    let r = await fetch('api.php?action=fetch&limit=20');
    const j = await r.json();
    const data = j.history;
    textarea.text("");
    if(data.length > 0){
        for(let item of data)
            log(`${item['author']}: ${item['word']} (${item['yomi']})`);
        const lastWord = data[data.length - 1];
        $("#lastWord").text(`${lastWord['word']} (${lastWord['yomi']})`);
        $("#nextLetter").text(lastWord['last_letter']);
        $('#counter').text(j['count']);
    } else {
        $("#lastWord").text("");
        $("#nextLetter").text("");
    }
}

let locked = false; // 連打防止

async function gameJoined(username) {
    search.hide();
    game.show();
    $("#userName").text(username);
    await reFetch();
    const nextWord = $("#nextWord");
    $("#wordSubmit").on('click', async () => {
        const word = nextWord.val();
        if(word === "") {
            alert.text('Please enter word');
            return;
        }
        if(locked) {
            alert.text('Please wait for a moment');
            return;
        }
        locked = true;

        try {

            let r = await fetch('api.php?action=word', {
                method: 'POST',
                body: new URLSearchParams({word: word})
            });
            setTimeout(() => {
                locked = false;
            }, 1000); // 1秒後に解除
            const j = await r.json();
            if (j.success) {
                nextWord.val('');
                alert.text('');
                await reFetch();
            } else {
                alert.text(j.error);
            }

        } catch (error) {
            alert.text('Error occurred while submitting the word');
        } finally {
            setTimeout(() => {
                locked = false;
            }, 1000); // 1秒後に解除
        }
    });
    nextWord.on('keydown', (e) => {
        if (e.key === 'Enter') $("#wordSubmit").trigger('click');
    });
    setInterval(reFetch, 2000);
}

fetch('api.php?action=login').then(async response => {
    let j = await response.json();
    if(j.success){
        await gameJoined(j.username);
    } else {
        $('#submitName').on('click', () => {
            alert.text("");
            const userName = $("#userNameInput").val();
            if(userName === "") {
                alert.text("Please enter a username");
                return;
            }
            fetch('api.php?action=login', {
                method: 'POST',
                body: new URLSearchParams({ username: userName })
            }).then(async response => {
                j = await response.json();
                if(j.success){
                    await gameJoined(j.username);
                } else {
                    alert.text(j.error);
                }
            });
        });
    }
});