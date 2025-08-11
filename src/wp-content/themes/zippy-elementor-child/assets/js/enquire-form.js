$(function () {
  $(".trigger-input-type").on("focus click", function () {
    if ($(this).attr("type") !== "date") {
      $(this).attr("type", "date");

      setTimeout(() => {
        this.showPicker?.();
        $(this).trigger("click");
      }, 0);
    }
  });

  $(".trigger-input-type").on("change", function () {
    $(this).attr("type", "text");
  });
});
