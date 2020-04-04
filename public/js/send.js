const sendButton = document.getElementById("messageSend");
const changeName = document.getElementsByClassName("chat-name-change")[0];
const messageContainer = document.getElementsByClassName("messages")[0];
let user = document.getElementsByClassName("hello")[0].getAttribute("data-id");
let hash = document.getElementById("messageSend").getAttribute("data-id");
if (sendButton) {
  sendButton.addEventListener("click", function() {
    messageSend();
    updateChat(hash);
  });
}
setInterval(function() {
  updateChat(hash);
}, 10000);

if (changeName) {
  document.getElementById("set").addEventListener("click", function() {
    let input = document.createElement("input");
    input.setAttribute("type", "text");

    let submit = document.createElement("button");
    submit.innerText = "Change name";
    let close = document.createElement("p");
    close.innerText = "X";
    close.classList.add("close");

    document.getElementById("set").style.display = "none";
    document.getElementsByClassName("chat-name-change")[0].style.height = "5vh";
    changeName.appendChild(input);
    changeName.appendChild(submit);
    changeName.appendChild(close);

    close.addEventListener("click", function() {
      close.removeEventListener;
      close.parentNode.removeChild(input);
      close.parentNode.removeChild(submit);
      close.parentNode.removeChild(close);
      document.getElementById("set").style.display = "";
      document.getElementsByClassName("chat-name-change")[0].style.height = "0";
    });
    submit.addEventListener("click", function() {
      nameSet(input.value);
    });
  });
}
function nameSet(name) {
  if (name) {
    $.ajax({
      type: "POST",
      url: `/chat/${hash}/name-set`,
      data: { name: name },
      success: function(data, dataType) {
        window.location.reload();
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert("Error occured. Check console to see error log.");
        console.log(errorThrown);
      }
    });
  } else {
    alert("You need to provide name");
  }
}

function messageSend() {
  let message = document.getElementById("messageContent");

  if (message) {
    $.ajax({
      type: "POST",
      url: `/chat/${hash}/send`,
      data: { message: message.value },
      success: function(data, dataType) {
        updateChat(hash);
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert("Error occured. Check console to see error log.");
        console.log(errorThrown);
      }
    });
    $("#messageContent").val("");
  } else {
    alert("You need to provide message");
  }
}

function updateChat(hash) {
  getJSON(`/chat/${hash}/json`, function(err, data) {
    if (err) {
      alert("Error occured. Check console to see error log");
      console.log(err);
    } else {
      displayMessages(JSON.parse(data));
      messageContainer.scrollTop = messageContainer.scrollHeight;
    }
  });
}
function displayMessages(messages) {
  messageContainer.innerHTML = "";
  messages.forEach(message => {
    let messageCont = document.createElement("div");
    messageCont.classList.add("message-object");
    messageCont.classList.add(
      user == message["authorId"] ? "sent" : "received"
    );
    let messageBubble = document.createElement("div");
    messageBubble.classList.add("message-content");
    messageBubble.innerText = message["content"];

    let author = document.createElement("p");
    author.classList.add("message-author");
    author.innerText = messageCont.classList.contains("sent")
      ? "Me"
      : message["author"];

    let sent = document.createElement("p");
    sent.classList.add("message-sent-at");
    sent.innerText = message["date"];

    messageCont.appendChild(author);
    messageCont.appendChild(messageBubble);
    messageCont.appendChild(sent);
    messageContainer.appendChild(messageCont);
  });
}
function getJSON(url, callback) {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", url, true);
  xhr.responseType = "json";
  xhr.onload = function() {
    var status = xhr.status;
    if (status === 200) {
      callback(null, xhr.response);
    } else {
      callback(status, xhr.response);
    }
  };
  xhr.send();
}
