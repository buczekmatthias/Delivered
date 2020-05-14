const sendButton = document.getElementById("messageSend"); //Submit button of messenger form
const changeName = document.getElementsByClassName("chat-name-change")[0]; //Container where will name change form go
const messageContainer = document.getElementsByClassName("messages")[0]; //Container where are messages displayed
const file = document.getElementById("messageFile"); //File button
const chatimg = document.getElementById("chatImage"); //Chat file button
const user = document
  .getElementsByClassName("user-nav-elem")[0]
  .getAttribute("data-id"); //User's id
const hash = document.getElementsByClassName("chat")[0].getAttribute("data-id"); //Chat's hash
const requestsElem = document.getElementById("chat-requests");

//Send and refetch messages from API
if (sendButton) {
  updateChat(hash);
  sendButton.addEventListener("click", () => {
    messageSend();
    updateChat(hash);
  });
  //Set auto chat refetch
  setInterval(() => {
    updateChat(hash);
  }, 10000);
}
if (requestsElem) {
  requestsElem.addEventListener("click", () => {
    requests(hash);
  });
}
if (file) {
  file.addEventListener("change", () => {
    if (file.files.length > 0) {
      let formData = new FormData();
      formData.append("file", file.files[0]);
      try {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", `/chat/${hash}/send`, true);
        xhr.send(formData);
        document.getElementById("messageFile").value = "";
      } catch (error) {
        alert("Error occured. Check console to see error status.");
        console.log(error);
      }
    }
  });
}
if (chatimg) {
  chatimg.addEventListener("change", () => {
    if (chatimg.files.length > 0) {
      let formData = new FormData();
      formData.append("file", chatimg.files[0]);
      try {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", `/chat/${hash}/set-image`, true);
        xhr.onload = () => {
          window.location.reload();
        };
        xhr.send(formData);
      } catch (error) {
        alert("Error occured. Check console to see error status.");
        console.log(error);
      }
    }
  });
}

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
    try {
      let xhr = new XMLHttpRequest();
      let fD = new FormData();
      fD.append("name", name);
      xhr.open("POST", `/chat/${hash}/name-set`, true);
      xhr.onload = () => {
        window.location.reload();
      };
      xhr.send(fD);
    } catch (error) {
      alert("Error occured. Check console to see error status.");
      console.log(error);
    }
  } else {
    alert("You need to provide name");
  }
}

function messageSend() {
  let message = document.getElementById("messageContent");
  //If message is not null make ajax request to send message and fetch messages
  if (message) {
    try {
      let xhr = new XMLHttpRequest();
      let fD = new FormData();
      fD.append("message", message.value);
      xhr.open("POST", `/chat/${hash}/send`, true);
      xhr.send(fD);
      document.getElementById("messageContent").value = "";
      updateChat(hash);
    } catch (error) {
      alert("Error occured. Check console to see error status.");
      console.log(error);
    }
  } else {
    alert("You need to provide message");
  }
}

function updateChat(hash) {
  //Get messages through internal API
  getJSON(`/chat/${hash}/json/messages`, (err, data) => {
    if (err) {
      alert("Error occured. Check console to see error status");
      console.log(err);
    } else {
      //Show taken messages and scroll to last message in chat
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

function requests(hash) {
  getJSON(`/chat/${hash}/json/request-list`, (err, data) => {
    if (err) {
      alert("Error occured. Check console to see error status");
      console.log(err);
    } else {
      //Show taken messages and scroll to last message in chat
      displayList(JSON.parse(data));
    }
  });
}

function displayList(list) {
  let bg = document.createElement("div");
  bg.classList.add("request-list-bg");
  bg.style.backgroundColor = "#000";
  bg.style.opacity = ".35";
  bg.style.zIndex = 24;
  bg.style.width = "100vw";
  bg.style.height = "100vh";
  bg.style.position = "absolute";
  bg.style.top = 0;
  bg.style.left = 0;

  document.body.insertBefore(bg, document.body.children[0]);
  let reqBox = document.createElement("div");
  reqBox.classList.add("request-box-list");

  let close = document.createElement("span");
  close.classList.add("close-elem");
  close.innerText = "Close";

  reqBox.appendChild(close);

  close.addEventListener("click", () => {
    window.location.href = `/chat/${hash}`;
  });

  list.forEach((requestor) => {
    let elem = document.createElement("div");
    elem.classList.add("member-request");
    elem.setAttribute("data-request-id", requestor["request-id"]);

    let img = document.createElement("img");
    img.classList.add("profile-img-request");
    img.src = `/images/${
      requestor["image"] ? "users/" + requestor["image"] : "man.png"
    }`;

    let name = document.createElement("span");
    name.classList.add("name-request");
    name.innerText = requestor["name"];

    let accept = document.createElement("span");
    accept.classList.add("accept");
    accept.innerText = "Accept";

    let decline = document.createElement("span");
    decline.classList.add("decline");
    decline.innerText = "Decline";

    accept.addEventListener("click", () => {
      let xhr = new XMLHttpRequest();
      xhr.open("POST", `/request/${requestor["request-id"]}/approve`);
      xhr.onload = () => {
        document.body.removeChild(bg);
        document.body.removeChild(reqBox);
        requests(hash);
      };
      xhr.send();
    });

    decline.addEventListener("click", () => {
      let xhr = new XMLHttpRequest();
      xhr.open("POST", `/request/${requestor["request-id"]}/remove`);
      xhr.onload = () => {
        document.body.removeChild(bg);
        document.body.removeChild(reqBox);
        requests(hash);
      };
      xhr.send();
    });

    elem.appendChild(img);
    elem.appendChild(name);
    elem.appendChild(accept);
    elem.appendChild(decline);
    reqBox.appendChild(elem);
  });
  document.body.insertBefore(reqBox, bg);
}

//XHR request to get data
function getJSON(url, callback) {
  let xhr = new XMLHttpRequest();
  xhr.open("GET", url, true);
  xhr.responseType = "json";
  xhr.onload = () => {
    let status = xhr.status;
    if (status === 200) {
      callback(null, xhr.response);
    } else {
      callback(status, xhr.response);
    }
  };
  xhr.send();
}
