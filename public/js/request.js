function createRequest(hash) {
  try {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", `/chat/${hash}/request`);
    xhr.onload = () => {
      window.location.href = "/";
    };
    xhr.send();
  } catch (error) {
    alert("Error ocurred. Check console for details");
    console.log(`Error given: ${error}`);
  }
}

const popup = document.getElementsByClassName("pop-join-request")[0];
if (popup) {
  let requestButton = document.getElementsByClassName("c-join")[0];
  requestButton.addEventListener("click", () => {
    createRequest(requestButton.getAttribute("data-hash"));
  });
}

const membersSelect = document.getElementsByClassName("form-members-add")[0];

membersSelect.addEventListener("click", () => {
  document
    .getElementsByClassName("ok-button")[0]
    .removeEventListener("click", okButton);
  let membersBox = document.getElementsByClassName("set-members")[0];
  membersBox.style.display = "block";

  let bg = document.createElement("div");
  bg.classList.add("form-member-bg");
  bg.style.backgroundColor = "#000";
  bg.style.opacity = ".35";
  bg.style.zIndex = 24;
  bg.style.width = "100vw";
  bg.style.height = "100vh";
  bg.style.position = "absolute";
  bg.style.top = 0;
  bg.style.left = 0;

  document.body.insertBefore(bg, document.body.children[0]);

  document
    .getElementsByClassName("ok-button")[0]
    .addEventListener("click", okButton);
});

function okButton() {
  document.body.removeChild(
    document.getElementsByClassName("form-member-bg")[0]
  );
  document.getElementsByClassName("set-members")[0].style.display = "none";
  document.getElementById(
    "js-members-counter"
  ).innerText = document.querySelectorAll(
    'input[type="checkbox"]:checked'
  ).length;
}
