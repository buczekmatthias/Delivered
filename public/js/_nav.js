const logout = document.getElementsByClassName("logout-btn")[0];

window.onload = () => {
  if (!window.localStorage.getItem("currentId")) {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/getUserId");
    xhr.onreadystatechange = () => {
      console.log(xhr.readyState);
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          window.localStorage.setItem(
            "currentId",
            JSON.parse(xhr.responseText)
          );
        } else {
          alert(
            "One of app functions had problems loading. Please refresh page and wait couple seconds before continuing use of application. Thank you!"
          );
        }
      }
    };
    xhr.send();
  }
};

logout.onclick = () => {
  window.localStorage.removeItem("currentId");
};
