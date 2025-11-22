/*!
  * Sweetweb v1.0.61 (https://websweetstudio.com)
  * Copyright 2013-2024 websweetstudio.com
  * Licensed under GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
  */
(function (factory) {
  typeof define === 'function' && define.amd ? define(factory) :
  factory();
})((function () { 'use strict';

  // Alpine.js initialization
  document.addEventListener('alpine:init', () => {
    // Global Alpine.js data and methods can be initialized here
    Alpine.data('portfolioApp', () => ({
      modalOpen: false,
      categories: [],
      selectedCategory: '',
      portfolios: [],
      filteredPortfolios: [],
      currentPage: 1,
      itemsPerPage: 12,
      previewOpen: false,
      currentPreview: null,
      
      init() {
        this.loadCategories();
        this.loadPortfolios();
      },
      
      loadCategories() {
        // Categories will be loaded via PHP and passed to Alpine.js
        const categoriesData = document.querySelector('#categories-data');
        if (categoriesData) {
          this.categories = JSON.parse(categoriesData.textContent);
        }
      },
      
      loadPortfolios() {
        // Portfolios will be loaded via PHP and passed to Alpine.js
        const portfoliosData = document.querySelector('#portfolios-data');
        if (portfoliosData) {
          this.portfolios = JSON.parse(portfoliosData.textContent);
          this.filteredPortfolios = [...this.portfolios];
        }
      },
      
      filterByCategory(category) {
        this.selectedCategory = category;
        this.currentPage = 1;
        
        if (category === '') {
          this.filteredPortfolios = [...this.portfolios];
        } else {
          this.filteredPortfolios = this.portfolios.filter(portfolio => 
            portfolio.jenis && portfolio.jenis.includes(category)
          );
        }
      },
      
      openPreview(portfolio) {
        this.currentPreview = portfolio;
        this.previewOpen = true;
      },
      
      closePreview() {
        this.previewOpen = false;
        this.currentPreview = null;
      },
      
      get paginatedPortfolios() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredPortfolios.slice(start, end);
      },
      
      get totalPages() {
        return Math.ceil(this.filteredPortfolios.length / this.itemsPerPage);
      },
      
      goToPage(page) {
        this.currentPage = page;
      }
    }));
  });

  // Fallback for browsers without Alpine.js
  document.addEventListener("DOMContentLoaded", function () {
    // Get the modal elements
    const modalTrigger = document.querySelector(".btn-modal-portofolio");
    const modal = document.querySelector(".frame-modal-portofolio");
    const closeModalBtn = document.querySelector(".close-modal-portofolio");

    if (modalTrigger && modal && closeModalBtn) {
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
    }
  });

}));
//# sourceMappingURL=script.js.map
