const amountCounter = document.getElementsByClassName("amount-counter")[0],
  membersBox = document.getElementsByClassName("new-members-box")[0],
  counter = document.getElementsByClassName("amount")[0],
  boxses = document.querySelectorAll("input[type='checkbox']"),
  close = document.getElementsByClassName("close")[0],
  save = document.getElementsByClassName("save")[0];

let amount = 0;

amountCounter.onclick = () => {
  membersBox.classList.add("show-box");
};

close.onclick = () => {
  if (membersBox.classList.contains("show-box"))
    membersBox.classList.remove("show-box");
};

save.onclick = () => {
  if (amount === 0)
    counter.innerHTML =
      "<p class='error-users'>You need to pick at least one user</p>";
  else {
    amountCounter.innerHTML = `${amount} user${amount === 1 ? "" : "s"}`;
    membersBox.classList.remove("show-box");
  }
};

Array.from(boxses).forEach((box) => {
  box.onclick = () => {
    if (box.checked) amount++;
    else amount--;

    counter.innerHTML =
      amount === 0
        ? "<p class='error-users'>You need to pick at least one user</p>"
        : `${amount} chosen`;
  };
});
