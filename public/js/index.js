const usrimg = document.getElementById("userImg"); //User file button

if (usrimg) {
  usrimg.addEventListener("change", () => {
    if (usrimg.files.length > 0) {
      let formData = new FormData();
      formData.append("profileImg", usrimg.files[0]);
      try {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", `/u/set-image`, true);
        xhr.onload = () => {
          window.location.reload();
        };
        xhr.send(formData);
      } catch (error) {
        alert("Error occured. Check console to see error log.");
        console.log(error);
      }
    }
  });
}
