const requestButton = document.getElementsByClassName("c-join")[0];

requestButton.addEventListener(
  "click",
  createRequest(requestButton.getAttribute("data-hash"))
);

function createRequest(hash) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", `/chat/${hash}`);
  xhr.send();
}
