const sendButton = document.getElementById("messageSend");
if (sendButton) {
  sendButton.addEventListener("click", function() {
    messageSend();
    updateChat();
  });
}

window.addEventListener("load", function() {
  updateChat();
});

function messageSend() {
  let message = document.getElementById("messageContent");

  $.ajax({
    type: "POST",
    url: "/chat/" + sendButton.dataset.id + "/send",
    data: { message: message.value },
    success: function(data, dataType) {
      window.location.reload();
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert("Error occured. Try again in a while.");
    }
  });
  $("#messageContent").val("");
}

function updateChat() {
  setInterval(function() {
    window.location.reload();
  }, 10000);
}
