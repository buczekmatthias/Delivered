const invitationsBtn = document.getElementsByClassName("invitations-btn")[0],
  invitationsParent = document.getElementsByClassName("invitations-list")[0]
    .parentElement,
  notificationsBtn = document.getElementsByClassName("notifications-btn")[0],
  notificationsParent = document.getElementsByClassName("notifications-list")[0]
    .parentElement,
  invitationsMenu = document.getElementsByClassName("invitations-menu")[0],
  requestFriendship = document.getElementsByClassName("request-user"),
  userCheck = `<path fill-rule="evenodd" d="M11 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM1.022 13h9.956a.274.274 0 00.014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 00.022.004zm9.974.056v-.002.002zM6 7a2 2 0 100-4 2 2 0 000 4zm3-2a3 3 0 11-6 0 3 3 0 016 0zm6.854.146a.5.5 0 010 .708l-3 3a.5.5 0 01-.708 0l-1.5-1.5a.5.5 0 01.708-.708L12.5 7.793l2.646-2.647a.5.5 0 01.708 0z" clip-rule="evenodd"></path>`,
  userPlus = `<path fill-rule="evenodd" d="M11 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM1.022 13h9.956a.274.274 0 00.014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 00.022.004zm9.974.056v-.002.002zM6 7a2 2 0 100-4 2 2 0 000 4zm3-2a3 3 0 11-6 0 3 3 0 016 0zm4.5 0a.5.5 0 01.5.5v2a.5.5 0 01-.5.5h-2a.5.5 0 010-1H13V5.5a.5.5 0 01.5-.5z" clip-rule="evenodd"></path><path fill-rule="evenodd" d="M13 7.5a.5.5 0 01.5-.5h2a.5.5 0 010 1H14v1.5a.5.5 0 01-1 0v-2z" clip-rule="evenodd"></path>`,
  acceptInv = document.getElementsByClassName("inv-accept"),
  deleteInv = document.getElementsByClassName("inv-refuse");

let toggleFirstNoti = (toggleFirstInv = false);

invitationsBtn.onclick = () => {
  if (notificationsParent.classList.contains("active"))
    notificationsParent.classList.remove("active");
  invitationsParent.classList.toggle("active");

  if (invitationsParent.classList.contains("active") && !toggleFirstInv) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/invitations/view");
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          toggleFirstInv = true;
          invitationsBtn.classList.remove("new");
        }
      }
    };
    xhr.send();
  }
};

notificationsBtn.onclick = () => {
  if (invitationsParent.classList.contains("active"))
    invitationsParent.classList.remove("active");
  notificationsParent.classList.toggle("active");

  if (notificationsParent.classList.contains("active") && !toggleFirstNoti) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/notifications/view");
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          toggleFirstNoti = true;
          notificationsBtn.classList.remove("new");
        }
      }
    };
    xhr.send();
  }
};

Array.from(requestFriendship).forEach((user) => {
  user.onclick = () => {
    let xhr = new XMLHttpRequest(),
      fd = new FormData();
    fd.append("userId", user.getAttribute("data-user-id"));
    xhr.open(
      "POST",
      user.classList.contains("requested-box")
        ? "/api/friends/remove-invitation"
        : "/api/friends/send-invitation"
    );
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          user.classList.toggle("requested-box");
          user.children[0].innerHTML = user.classList.contains("requested-box")
            ? userCheck
            : userPlus;
        } else {
          alert(xhr.responseText);
        }
      }
    };
    xhr.send(fd);
  };
});

Array.from(acceptInv).forEach((accept) => {
  accept.onclick = () => {
    let xhr = new XMLHttpRequest(),
      fd = new FormData();
    fd.append(
      "invitationId",
      accept.parentElement.getAttribute("data-invitation-id")
    );
    xhr.open("POST", "/api/friends/invitation/accept");
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          accept.parentElement.innerHTML = "You accepted invitation";
        } else {
          alert(xhr.responseText);
        }
      }
    };
    xhr.send(fd);
  };
});

Array.from(deleteInv).forEach((deleteBtn) => {
  deleteBtn.onclick = () => {
    let xhr = new XMLHttpRequest(),
      fd = new FormData();
    fd.append(
      "invitationId",
      deleteBtn.parentElement.getAttribute("data-invitation-id")
    );
    xhr.open("POST", "/api/friends/invitation/refuse");
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          document
            .getElementsByClassName("invitations-list")[0]
            .removeChild(deleteBtn.parentElement.parentElement.parentElement);
        } else {
          alert(xhr.responseText);
        }
      }
    };
    xhr.send(fd);
  };
});
