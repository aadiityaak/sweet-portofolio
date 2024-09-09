/*!
  * Sweetweb v1.0.605 (https://websweetstudio.com)
  * Copyright 2013-2024 websweetstudio.com
  * Licensed under GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
  */
(function (factory) {
  typeof define === 'function' && define.amd ? define(factory) :
  factory();
})((function () { 'use strict';

  document.addEventListener("DOMContentLoaded", function () {
    // Get the modal elements
    const modalTrigger = document.querySelector(".btn-modal-portofolio");
    const modal = document.querySelector(".frame-modal-portofolio");
    const closeModalBtn = document.querySelector(".close-modal-portofolio");

    // Function to open the modal
    function openModal() {
      modal.style.display = "block";
    }

    // Function to close the modal
    function closeModal() {
      modal.style.display = "none";
    }

    // Event listener to open the modal when button is clicked
    modalTrigger.addEventListener("click", openModal);

    // Event listener to close the modal when close button is clicked
    closeModalBtn.addEventListener("click", closeModal);

    // Event listener to close the modal when clicking outside of the modal content
    window.addEventListener("click", function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });
  });

}));
//# sourceMappingURL=script.js.map
