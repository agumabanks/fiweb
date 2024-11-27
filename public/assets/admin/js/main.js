/*============================================
    File Upload
  ==============================================*/
  $(window).on("load", function () {
    $(".upload-file__input").on("change", function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            let img = $(this).siblings(".upload-file__img").find("img");

            reader.onload = function (e) {
                img.attr("src", e.target.result);
            };

            reader.readAsDataURL(this.files[0]);

            reader.addEventListener("progress", (event) => {
                if (event.loaded && event.total) {
                    const percent = (event.loaded / event.total) * 100;
                    $("#uploadProgress").val(percent);
                    $("#progress-label").html(Math.round(percent) + "%");
                    $("#name_of_file").html(this.files[0].name);
                }
            });
        }
    });
});
