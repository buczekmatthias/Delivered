const socket = new WebSocket("ws://localhost:8001"),
  currentId = parseInt(window.localStorage.getItem("currentId")),
  currentName = window.localStorage.getItem("currentName"),
  currentImage = window.localStorage.getItem("currentImage"),
  messagesContainer = document.getElementsByClassName("messages-container")[0],
  messageInput = document.getElementsByClassName("message-content")[0],
  linksIcon = document.getElementsByClassName("links-icon")[0],
  linksBox = document.getElementsByClassName("links-box")[0],
  filesCounter = document.getElementsByClassName("files-counter")[0],
  filesInput = document.getElementsByClassName("files-input")[0],
  sendIcon = document.getElementsByClassName("send-icon")[0],
  rotate = `<path d="M512 1024c-69.1 0-136.2-13.5-199.3-40.2C251.7 958 197 921 150 874c-47-47-84-101.7-109.8-162.7C13.5 648.2 0 581.1 0 512c0-19.9 16.1-36 36-36s36 16.1 36 36c0 59.4 11.6 117 34.6 171.3 22.2 52.4 53.9 99.5 94.3 139.9 40.4 40.4 87.5 72.2 139.9 94.3C395 940.4 452.6 952 512 952c59.4 0 117-11.6 171.3-34.6 52.4-22.2 99.5-53.9 139.9-94.3 40.4-40.4 72.2-87.5 94.3-139.9C940.4 629 952 571.4 952 512c0-59.4-11.6-117-34.6-171.3a440.45 440.45 0 0 0-94.3-139.9 437.71 437.71 0 0 0-139.9-94.3C629 83.6 571.4 72 512 72c-19.9 0-36-16.1-36-36s16.1-36 36-36c69.1 0 136.2 13.5 199.3 40.2C772.3 66 827 103 874 150c47 47 83.9 101.8 109.7 162.7 26.7 63.1 40.2 130.2 40.2 199.3s-13.5 136.2-40.2 199.3C958 772.3 921 827 874 874c-47 47-101.8 83.9-162.7 109.7-63.1 26.8-130.2 40.3-199.3 40.3z"></path>`,
  send = `<defs></defs>
  <path d="M931.4 498.9L94.9 79.5c-3.4-1.7-7.3-2.1-11-1.2-8.5 2.1-13.8 10.7-11.7 19.3l86.2 352.2c1.3 5.3 5.2 9.6 10.4 11.3l147.7 50.7-147.6 50.7c-5.2 1.8-9.1 6-10.3 11.3L72.2 926.5c-0.9 3.7-0.5 7.6 1.2 10.9 3.9 7.9 13.5 11.1 21.5 7.2l836.5-417c3.1-1.5 5.6-4.1 7.2-7.1 3.9-8 0.7-17.6-7.2-21.6zM170.8 826.3l50.3-205.6 295.2-101.3c2.3-0.8 4.2-2.6 5-5 1.4-4.2-0.8-8.7-5-10.2L221.1 403 171 198.2l628 314.9-628.2 313.2z" pid="14020"></path>`;

socket.onopen = () => {
  getChatMessages();
};

socket.onmessage = (e) => {
  appendMessage(JSON.parse(e.data));
};

socket.onerror = () => {
  alert(
    "Something failed. Reload page and try again. If this will repeat up to 3 times then you have to wait for service to be back in usage."
  );
};

socket.onclose = () => {};

sendIcon.onclick = () => {
  if (messageInput.value !== "" || filesInput.files.length > 0) {
    sendIcon.innerHTML = rotate;
    sendIcon.classList.add("rotate");
    sendMessage();
  }
};

filesInput.onchange = () => {
  if (filesInput.files.length > 0) {
    if (!filesCounter.classList.contains("selected-files")) {
      filesCounter.classList.add("selected-files");
    }
    filesCounter.innerHTML = filesInput.files.length;
  } else {
    if (filesCounter.classList.contains("selected-files")) {
      filesCounter.classList.remove("selected-files");
    }
  }
};

function clearInputs() {
  messageInput.value = "";
  filesInput.value = "";
  if (filesCounter.classList.contains("selected-files")) {
    filesCounter.classList.remove("selected-files");
  }
  sendIcon.classList.remove("rotate");
  sendIcon.innerHTML = send;
}

function sendMessage() {
  let message = new FormData();

  message.append("content", messageInput.value);
  if (filesInput.files.length > 0) {
    for (let i = 0; i < filesInput.files.length; i++) {
      message.append("file[]", filesInput.files[i]);
    }
  }

  let xhr = new XMLHttpRequest();
  xhr.open(
    "POST",
    `/api/messages/send/${parseInt(window.location.pathname.split("/").pop())}`
  );
  xhr.onreadystatechange = () => {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        clearInputs();
        appendMessage(JSON.parse(xhr.responseText));
        socket.send(xhr.response);
      } else {
        sendIcon.classList.remove("rotate");
        sendIcon.innerHTML = send;
        alert(xhr.responseText);
      }
    }
  };
  xhr.send(message);
}

function getChatMessages() {
  let xhr = new XMLHttpRequest();
  xhr.open(
    "GET",
    `/api/messages/get/${parseInt(window.location.pathname.split("/").pop())}`
  );
  xhr.onreadystatechange = () => {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        displayMessages(JSON.parse(xhr.responseText));
      } else {
        alert(xhr.responseText);
      }
    }
  };
  xhr.send();
}

function displayMessages(messages) {
  messagesContainer.innerHTML = "";

  messages.forEach((message) => {
    let messageCont = document.createElement("div");
    messageCont.classList.add("message-container");
    messageCont.classList.add(
      message.sender.id === currentId ? "sent" : "received"
    );

    messageCont.innerHTML = `<p class="sender"><img src='${
      message.sender.img
    }'><span>${message.sender.name}</span></p><p class="text">${
      message.content.text
    }</p>${
      message.content.files.length > 0
        ? `<p class="files"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewbox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
    <path d="M854.6 288.6L639.4 73.4c-6-6-14.1-9.4-22.6-9.4H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V311.3c0-8.5-3.4-16.7-9.4-22.7zM790.2 326H602V137.8L790.2 326zm1.8 562H232V136h302v216a42 42 0 0 0 42 42h216v494z"></path>
  </svg><span>${message.content.files.length}</span></p>`
        : ``
    }${
      message.content.links.length > 0
        ? `<p class="links"><svg class="links-icon" stroke="currentColor" fill="currentColor" stroke-width="0" viewbox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
  <path d="M574 665.4a8.03 8.03 0 0 0-11.3 0L446.5 781.6c-53.8 53.8-144.6 59.5-204 0-59.5-59.5-53.8-150.2 0-204l116.2-116.2c3.1-3.1 3.1-8.2 0-11.3l-39.8-39.8a8.03 8.03 0 0 0-11.3 0L191.4 526.5c-84.6 84.6-84.6 221.5 0 306s221.5 84.6 306 0l116.2-116.2c3.1-3.1 3.1-8.2 0-11.3L574 665.4zm258.6-474c-84.6-84.6-221.5-84.6-306 0L410.3 307.6a8.03 8.03 0 0 0 0 11.3l39.7 39.7c3.1 3.1 8.2 3.1 11.3 0l116.2-116.2c53.8-53.8 144.6-59.5 204 0 59.5 59.5 53.8 150.2 0 204L665.3 562.6a8.03 8.03 0 0 0 0 11.3l39.8 39.8c3.1 3.1 8.2 3.1 11.3 0l116.2-116.2c84.5-84.6 84.5-221.5 0-306.1zM610.1 372.3a8.03 8.03 0 0 0-11.3 0L372.3 598.7a8.03 8.03 0 0 0 0 11.3l39.6 39.6c3.1 3.1 8.2 3.1 11.3 0l226.4-226.4c3.1-3.1 3.1-8.2 0-11.3l-39.5-39.6z"></path>
</svg><span>${message.content.links.length}</span></p>`
        : ``
    }<p class="send-date">${message.date}</p>`;

    messagesContainer.appendChild(messageCont);
  });

  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function appendMessage(data) {
  let messageCont = document.createElement("div");
  messageCont.classList.add("message-container");
  messageCont.classList.add(data.sender.id === currentId ? "sent" : "received");

  messageCont.innerHTML = `<p class="sender"><img src='${
    data.sender.img
  }'><span>${data.sender.name}</span></p><p class="text">${
    data.content.text
  }</p>${
    data.content.files.length > 0
      ? `<p class="files"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewbox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
  <path d="M854.6 288.6L639.4 73.4c-6-6-14.1-9.4-22.6-9.4H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V311.3c0-8.5-3.4-16.7-9.4-22.7zM790.2 326H602V137.8L790.2 326zm1.8 562H232V136h302v216a42 42 0 0 0 42 42h216v494z"></path>
</svg><span>${data.content.files.length}</span></p>`
      : ``
  }${
    data.content.links.length > 0
      ? `<p class="links"><svg class="links-icon" stroke="currentColor" fill="currentColor" stroke-width="0" viewbox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
<path d="M574 665.4a8.03 8.03 0 0 0-11.3 0L446.5 781.6c-53.8 53.8-144.6 59.5-204 0-59.5-59.5-53.8-150.2 0-204l116.2-116.2c3.1-3.1 3.1-8.2 0-11.3l-39.8-39.8a8.03 8.03 0 0 0-11.3 0L191.4 526.5c-84.6 84.6-84.6 221.5 0 306s221.5 84.6 306 0l116.2-116.2c3.1-3.1 3.1-8.2 0-11.3L574 665.4zm258.6-474c-84.6-84.6-221.5-84.6-306 0L410.3 307.6a8.03 8.03 0 0 0 0 11.3l39.7 39.7c3.1 3.1 8.2 3.1 11.3 0l116.2-116.2c53.8-53.8 144.6-59.5 204 0 59.5 59.5 53.8 150.2 0 204L665.3 562.6a8.03 8.03 0 0 0 0 11.3l39.8 39.8c3.1 3.1 8.2 3.1 11.3 0l116.2-116.2c84.5-84.6 84.5-221.5 0-306.1zM610.1 372.3a8.03 8.03 0 0 0-11.3 0L372.3 598.7a8.03 8.03 0 0 0 0 11.3l39.6 39.6c3.1 3.1 8.2 3.1 11.3 0l226.4-226.4c3.1-3.1 3.1-8.2 0-11.3l-39.5-39.6z"></path>
</svg><span>${data.content.links.length}</span></p>`
      : ``
  }<p class="send-date">${data.date}</p>`;

  messagesContainer.appendChild(messageCont);

  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}
