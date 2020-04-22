const sendButton = document.getElementById("messageSend"); //Submit button of messenger form
const changeName = document.getElementsByClassName("chat-name-change")[0]; //Container where will name change form go
const messageContainer = document.getElementsByClassName("messages")[0]; //Container where are messages displayed
let user = document.getElementsByClassName("hello")[0].getAttribute("data-id"); //User's id
let hash = document.getElementsByClassName("chat")[0].getAttribute("data-id"); //Chat's hash
let file = document.getElementById("messageFile"); //File button
//Send and refetch messages from API
if (sendButton) {
  sendButton.addEventListener("click", () => {
    messageSend();
    updateChat(hash);
  });
}
if (file) {
  file.addEventListener("change", () => {
    if (file.files.length > 0) {
      let img = file.files[0];
      let formData = new FormData();
      formData.append("file", img);
      try {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", `/chat/${hash}/send`, true);
        xhr.send(formData);
        $("#messageFile").val("");
      } catch (error) {
        alert("Error occured. Check console to see error log.");
        console.log(error);
      }
    }
  });
}
//Set auto chat refetch
setInterval(() => {
  updateChat(hash);
}, 10000);

if (changeName) {
  //Change name form activation
  document.getElementById("set").addEventListener("click", () => {
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

    close.addEventListener("click", () => {
      close.removeEventListener;
      close.parentNode.removeChild(input);
      close.parentNode.removeChild(submit);
      close.parentNode.removeChild(close);
      document.getElementById("set").style.display = "";
      document.getElementsByClassName("chat-name-change")[0].style.height = "0";
    });
    submit.addEventListener("click", () => {
      nameSet(input.value);
    });
  });
}
function nameSet(name) {
  //If name is not null make ajax request to update name and reload
  if (name) {
    $.ajax({
      type: "POST",
      url: `/chat/${hash}/name-set`,
      data: { name: name },
      success: (data, dataType) => {
        window.location.reload();
      },
      error: (XMLHttpRequest, textStatus, errorThrown) => {
        alert("Error occured. Check console to see error log.");
        console.log(errorThrown);
      },
    });
  } else {
    alert("You need to provide name");
  }
}

function messageSend() {
  let message = document.getElementById("messageContent");
  //If message is not null make ajax request to send message and fetch messages
  if (message) {
    $.ajax({
      type: "POST",
      url: `/chat/${hash}/send`,
      data: { message: message.value },
      success: (data, dataType) => {
        $("#messageContent").val("");
        updateChat(hash);
      },
      error: (XMLHttpRequest, textStatus, errorThrown) => {
        alert("Error occured. Check console to see error log.");
        console.log(errorThrown);
      },
    });
  } else {
    alert("You need to provide message");
  }
}

function updateChat(hash) {
  //Fetch messages through internal API
  getJSON(`/chat/${hash}/json`, (err, data) => {
    if (err) {
      alert("Error occured. Check console to see error log");
      console.log(err);
    } else {
      //Show fetched messages and scroll to last message in chat
      displayMessages(JSON.parse(data));
      messageContainer.scrollTop = messageContainer.scrollHeight;
    }
  });
}
function displayMessages(messages) {
  //Clear container
  messageContainer.innerHTML = "";
  //Display all messages
  messages.forEach((message) => {
    let messageCont = document.createElement("div");
    messageCont.classList.add("message-object");
    messageCont.classList.add(
      user == message["authorId"] ? "sent" : "received"
    );
    let messageBubble = document.createElement("div");
    if (message["content"]) {
      messageBubble.classList.add("message-content");
      messageBubble.innerText = message["content"];
    }
    if (message["file"]) {
      messageBubble.classList.add("message-file");
      messageBubble.innerHTML = `<a href="/images/chatFiles/${message["file"]}" download><object data="/images/chatFiles/${message["file"]}"></object></a>`;
    }

    let author = document.createElement("p");
    author.classList.add("message-author");
    if (!messageCont.classList.contains("sent"))
      author.innerText = message["author"];

    let sent = document.createElement("p");
    sent.classList.add("message-sent-at");
    sent.innerText = message["date"];

    messageCont.appendChild(author);
    messageCont.appendChild(messageBubble);
    messageCont.appendChild(sent);
    messageContainer.appendChild(messageCont);
  });
}

//XHR request to get data
function getJSON(url, callback) {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", url, true);
  xhr.responseType = "json";
  xhr.onload = () => {
    var status = xhr.status;
    if (status === 200) {
      callback(null, xhr.response);
    } else {
      callback(status, xhr.response);
    }
  };
  xhr.send();
}
