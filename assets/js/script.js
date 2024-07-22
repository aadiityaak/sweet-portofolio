/*!
  * Sweetweb v1.0.4 (https://websweetstudio.com)
  * Copyright 2013-2024 websweetstudio.com
  * Licensed under GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
  */
(function (factory) {
  typeof define === 'function' && define.amd ? define(factory) :
  factory();
})((function () { 'use strict';

  document.addEventListener("DOMContentLoaded", function () {
    // Open modal
    document.querySelector('[data-bs-toggle="modal"]').addEventListener("click", function () {
      document.querySelector(this.getAttribute("data-bs-target")).style.display = "block";
    });

    // Close modal
    document.querySelector(".close-modal-portofolio").addEventListener("click", function () {
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

}));
//# sourceMappingURL=script.js.map
