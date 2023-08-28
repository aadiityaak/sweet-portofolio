jQuery(document).ready(function ($) {
  // Fungsi untuk memuat iframe secara lazy saat modal ditampilkan
  $(".lazy-load-modal").on("shown.bs.modal", function () {
    var modal = $(this);
    var spinner = modal.find(".spinner-border");
    var iframe = modal.find(".lazy-iframe");

    if (iframe.length > 0) {
      var src = iframe.data("src");
      spinner.hide(); // Sembunyikan spinner
      iframe.attr("src", src).removeClass("lazy-iframe");
    }
  });
});
