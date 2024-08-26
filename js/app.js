document.addEventListener('DOMContentLoaded', () => {

    const text = document.querySelector("#text");
    const uname = document.querySelector("#name")
    const last = document.querySelector(".last")

    // ---------- WEBSOCKET ----------
    // Tworzenie połączenia WebSocket
    const socket = new WebSocket('ws://localhost:8080');


    // Funkcja obsługująca otwarcie połączenia
    socket.onopen = () => {
        console.log('Połączenie z WebSocket zostało otwarte.');
    };

    // Funkcja obsługująca przychodzące wiadomości
    socket.onmessage = (event) => {
        let socketData = JSON.parse(event.data)
        console.log(socketData);
        if(socketData["action"] == "message-send"){
        text.value = socketData["message"];
        last.innerHTML = socketData["username"]
        }
    };

    // Funkcja obsługująca zamknięcie połączenia
    socket.onclose = () => {
        console.log('Połączenie z WebSocket zostało zamknięte.');
    };

    // Funkcja obsługująca błędy połączenia
    socket.onerror = (error) => {
        console.error('Błąd WebSocket:', error);
    };
    // --------------------------------

if(localStorage.getItem("uname") != null){
    uname.value = localStorage.getItem("uname")
}

uname.addEventListener("input", ()=>{
    localStorage.setItem("uname", uname.value)
})

text.addEventListener("input", ()=>{
    let content = text.value;
    let username = uname.value
    socket.send(JSON.stringify(
        {
            action: "message-send",
            username: username,
            message: content
        }));
})


});
