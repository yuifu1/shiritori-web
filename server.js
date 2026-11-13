let express = require('express');
let app = express();
const io = require('socket.io');
const PORT = 3000;

let users = {};
let words = {};
let roomIds = [];
let idStore = {};
let nameStore = {};

function getRandomInt(min, max) {
    const minCeiled = Math.ceil(min);
    const maxFloored = Math.floor(max);
    return Math.floor(Math.random() * (maxFloored - minCeiled) + minCeiled); // 上限は除き、下限は含む
}
function generateId() {
    let roomId = getRandomInt(10000, 100000).toString();
    while(roomIds.includes(roomId)) roomId = getRandomInt(10000, 100000).toString();
    roomIds.push(roomId);
    return roomId;
}

const s = app.listen(PORT, () => {
   console.log(`Listening on port ${PORT}`);
});

const ser = new io.Server(s);

ser.on('connection', (socket) => {
   console.log("Client connected");

   socket.on('disconnect', (reason) => {
        console.log("Client disconnected with reason ", reason);
        const roomId = idStore[socket.id];
        if(roomId) {
            delete idStore[socket.id];
            delete nameStore[socket.id];
            users[roomId] = users[roomId].filter(u => nameStore[socket.id] !== u);
            // ser.to(roomId).emit('delRoom', roomId);
            // ser.to(roomId).disconnectSockets(true);
            // roomIds = roomIds.filter(id => id !== roomId);
            // console.log(`Room ${roomId} deleted`);
        }
   });

   socket.on('createRoom', (user) => {
       console.log('create room');
       const roomId = generateId();
       socket.join(roomId);
       idStore[socket.id] = roomId;
       nameStore[socket.id] = user;
       users[roomId] = [user];
       words[roomId] = '';
       console.log("Room created");
       socket.emit('roomJoined', [roomId, '']);
   });

   socket.on('joinRoom', (roomId, user) => {
      if(roomId.includes(roomId) === false) {
          socket.emit('joinFailed', 'Room Unavailable');
          return;
      }

      socket.join(roomId);
      users[roomId].push(user);
      idStore[socket.id] = roomId;
      nameStore[socket.id] = user;
      console.log("Room joined");
      socket.emit('roomJoined', [roomId, words[roomId]]);

   });

   socket.on('sayWord', (word) => {
      const roomId = idStore[socket.id];
      if(roomId){
          if(word.at(1) === words[roomId].at(-1) || words[roomId] === ''){
              words[roomId] = word;
              ser.to(roomId).emit('word', word, nameStore[socket.id], word);
          } else {
              ser.to(roomId).emit('word', word, nameStore[socket.id], words[roomId], 'Rule Ignored');
          }
      }
   });

});

