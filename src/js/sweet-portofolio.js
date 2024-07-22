document.addEventListener("DOMContentLoaded", function () {
  // Open modal
  document
    .querySelector('[data-bs-toggle="modal"]')
    .addEventListener("click", function () {
      document.querySelector(
        this.getAttribute("data-bs-target")
      ).style.display = "block";
    });

  // Close modal
  document.querySelector(".btn-close").addEventListener("click", function () {
    this.closest(".modal").style.display = "none";
  });

  // Close modal on clicking outside
  window.addEventListener("click", function (event) {
    const modals = document.querySelectorAll(".modal");
    modals.forEach(function (modal) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
  });
});
