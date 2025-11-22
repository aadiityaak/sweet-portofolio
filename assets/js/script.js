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
    // Modal component for category selection
    Alpine.data('categoryModal', () => ({
      modalOpen: false,
      categories: [],
      selectedCategory: '',
      
      init() {
        const categoriesData = document.querySelector('#categories-data');
        if (categoriesData) {
          this.categories = JSON.parse(categoriesData.textContent);
        }
      },
      
      selectCategory(categorySlug) {
        this.selectedCategory = categorySlug;
        this.modalOpen = false;
        
        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('jenis_web', categorySlug);
        url.searchParams.delete('halaman'); // Reset to page 1
        window.history.pushState({}, '', url);
        
        // Trigger a custom event to notify the portfolio component
        window.dispatchEvent(new CustomEvent('categoryChanged', { detail: { category: categorySlug } }));
      }
    }));
    
    // Portfolio component for filtering and pagination
    Alpine.data('portfolioGrid', (initialPage = 1, initialCategory = '', showTitle = 'yes', styleThumbnail = '', previewPage = '', whatsappNumber = '', portofolioCredit = '', portofolioSelection = []) => ({
      filterFormOpen: false,
      portfolios: [],
      filteredPortfolios: [],
      currentPage: initialPage,
      itemsPerPage: 12,
      selectedCategory: initialCategory,
      showTitle: showTitle,
      styleThumbnail: styleThumbnail,
      previewPage: previewPage,
      whatsappNumber: whatsappNumber,
      portofolioCredit: portofolioCredit,
      portofolioSelection: portofolioSelection,
      
      init() {
        const portfoliosData = document.querySelector('#portfolios-data');
        if (portfoliosData) {
          this.portfolios = JSON.parse(portfoliosData.textContent);
          this.filterPortfolios();
        }
        
        // Listen for URL changes
        window.addEventListener('popstate', () => {
          this.updateFromURL();
        });
        
        // Listen for category changes from modal
        window.addEventListener('categoryChanged', (event) => {
          this.selectedCategory = event.detail.category;
          this.currentPage = 1; // Reset to page 1
          this.filterPortfolios();
        });
        
        // Initial filter
        this.updateFromURL();
      },
      
      updateFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        this.selectedCategory = urlParams.get('jenis_web') || '';
        this.currentPage = parseInt(urlParams.get('halaman')) || 1;
        this.filterPortfolios();
      },
      
      filterPortfolios() {
        // Reset to page 1 when filter changes
        this.currentPage = 1;
        
        if (this.selectedCategory && this.selectedCategory !== '') {
          this.filteredPortfolios = this.portfolios.filter(portfolio => 
            portfolio.jenis && portfolio.jenis.includes(this.selectedCategory)
          );
        } else if (this.portofolioSelection && this.portofolioSelection.length > 0) {
          this.filteredPortfolios = this.portfolios.filter(portfolio => 
            portfolio.jenis && this.portofolioSelection.includes(portfolio.jenis)
          );
        } else {
          this.filteredPortfolios = [...this.portfolios];
        }
        
        // Update URL when filter changes
        this.updateURL();
      },
      
      goToPage(page) {
        this.currentPage = page;
        this.updateURL();
      },
      
      updateURL() {
        const url = new URL(window.location);
        if (this.selectedCategory && this.selectedCategory !== '') {
          url.searchParams.set('jenis_web', this.selectedCategory);
        } else {
          url.searchParams.delete('jenis_web');
        }
        url.searchParams.set('halaman', this.currentPage);
        window.history.pushState({}, '', url);
      },
      
      get paginatedPortfolios() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredPortfolios.slice(start, end);
      },
      
      get totalPages() {
        return Math.ceil(this.filteredPortfolios.length / this.itemsPerPage);
      },
      
      getWhatsAppUrl(portfolio) {
        if (!this.whatsappNumber) return '#';
        const message = 'Saya tertarik dengan ' + portfolio.title;
        return 'https://wa.me/' + this.whatsappNumber + '?text=' + encodeURIComponent(message);
      },
      
      getPreviewUrl(portfolio) {
        return this.previewPage + '?id=' + portfolio.id;
      },
      
      getImageUrl(portfolio) {
        return this.styleThumbnail === 'thumbnail' ? portfolio.thumbnail_url : portfolio.screenshot;
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
