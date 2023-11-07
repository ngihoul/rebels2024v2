if (document.getElementById("profile_picture_input")) {
  const profilePictureInput = document.getElementById("profile_picture_input");

  const previewPicture = () => {
    const file = profilePictureInput.files;
    if (file) {
      const fileReader = new FileReader();
      const previewImage = document.getElementById("previewImage");

      fileReader.onload = function (event) {
        previewImage.setAttribute("src", event.target.result);
      };

      fileReader.readAsDataURL(file[0]);
    }
  };

  profilePictureInput.addEventListener("change", previewPicture);
}
