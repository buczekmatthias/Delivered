const removeFriendBtns = document.getElementsByClassName("friend-remove"),
  friendsList = document.getElementsByClassName("friends-list")[0],
  counter = document.getElementsByClassName("friends-counter")[0];

Array.from(removeFriendBtns).forEach((btn) => {
  console.log(counter.innerHTML, typeof par(counter.innerHTML));
  btn.onclick = () => {
    let xhr = new XMLHttpRequest(),
      fd = new FormData();
    xhr.open("POST", "/api/friends/remove-friend");
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          friendsList.removeChild(btn.parentElement.parentElement);
          counter.innerHTML =
            counter.innerHTML > 0 ? 0 : parseInt(counter.innerHTML) - 1;
        } else alert(xhr.responseText);
      }
    };
    fd.append("friendId", btn.getAttribute("data-friend-id"));
    xhr.send(fd);
  };
});
