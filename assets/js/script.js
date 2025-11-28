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
    console.log('Alpine.js initialized');
    // Modal component for category selection
    Alpine.data('categoryModal', () => ({
      modalOpen: false,
      categories: [],
      selectedCategory: '',
      
      init() {
        console.log('Initializing category modal');
        const categoriesData = document.querySelector('#categories-data');
        if (categoriesData) {
          try {
            this.categories = JSON.parse(categoriesData.textContent) || [];
            console.log('Categories data loaded:', this.categories);
          } catch (e) {
            console.error('Error parsing categories data:', e);
            this.categories = [];
          }
        }
        
        // Check if there's a category in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const categoryFromUrl = urlParams.get('jenis_web');
        if (categoryFromUrl) {
          this.selectedCategory = categoryFromUrl;
        }
      },
      
      selectCategory(categorySlug) {
        console.log('Category selected:', categorySlug);
        console.log('Category object:', this.categories.find(cat => cat.slug === categorySlug));
        
        // Close modal immediately
        this.modalOpen = false;
        
        // Update selected category
        this.selectedCategory = categorySlug;
        
        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('jenis_web', categorySlug);
        url.searchParams.delete('halaman'); // Reset to page 1
        window.history.pushState({}, '', url);
        
        // Trigger a custom event to notify the portfolio component
        console.log('Dispatching categoryChanged event');
        window.dispatchEvent(new CustomEvent('categoryChanged', { detail: { category: categorySlug } }));
        
        // Force a page reload to ensure the portfolio grid is updated
        setTimeout(() => {
          window.location.reload();
        }, 100);
      }
    }));
    
    // Portfolio component for filtering and pagination
    Alpine.data('portfolioGrid', (initialPage = 1, initialCategory = '', showTitle = 'yes', styleThumbnail = '', previewPage = '', whatsappNumber = '', portofolioCredit = '', portofolioSelection = []) => ({
      filterFormOpen: false,
      portfolios: [],
      filteredPortfolios: [],
      currentPage: initialPage,
      itemsPerPage: 12, // Fixed value
      selectedCategory: initialCategory,
      showTitle: showTitle,
      styleThumbnail: styleThumbnail,
      previewPage: previewPage,
      whatsappNumber: whatsappNumber,
      portofolioCredit: portofolioCredit,
      portofolioSelection: portofolioSelection,
      
      init() {
        console.log('portfolioGrid init with itemsPerPage:', this.itemsPerPage);

        const portfoliosData = document.querySelector('#portfolios-data');
        if (portfoliosData) {
          try {
            const parsedData = JSON.parse(portfoliosData.textContent) || [];

            // Check if parsed data contains an error
            if (parsedData.length === 1 && parsedData[0] && parsedData[0].code === 'rest_forbidden') {
              console.error('API returned forbidden error:', parsedData[0].message);
              this.portfolios = [];
            } else {
              this.portfolios = parsedData;
            }

            this.filterPortfolios();
          } catch (e) {
            console.error('Error parsing portfolios data:', e);
            this.portfolios = [];
          }
        }
        
        // Listen for URL changes
        window.addEventListener('popstate', () => {
          console.log('popstate event detected');
          this.updateFromURL();
        });
        
        // Listen for category changes from modal
        window.addEventListener('categoryChanged', (event) => {
          console.log('categoryChanged event received:', event.detail.category);
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
        console.log('updateFromURL: category =', this.selectedCategory, ', page =', this.currentPage);
        
        // Update the select dropdown if it exists
        const categoryFilter = document.getElementById('category-filter');
        if (categoryFilter) {
          categoryFilter.value = this.selectedCategory;
        }
        
        this.filterPortfolios();
      },
      
      filterPortfolios() {
        // Reset to page 1 when filter changes
        this.currentPage = 1;

        // Ensure portfolios is an array
        if (!Array.isArray(this.portfolios)) {
          this.portfolios = [];
        }
        
        console.log('Filtering portfolios. Selected category:', this.selectedCategory);
        console.log('Total portfolios:', this.portfolios.length);
        console.log('Portfolio selection:', this.portofolioSelection);
        
        // Debug: Print first portfolio to understand structure
        if (this.portfolios.length > 0) {
          console.log('First portfolio structure:', this.portfolios[0]);
        }
        
        // Check if portfolios data is valid
        if (this.portfolios.length === 0 || (this.portfolios.length === 1 && this.portfolios[0] && this.portfolios[0].code === 'rest_forbidden')) {
          console.log('Invalid portfolios data detected, using empty array');
          this.filteredPortfolios = [];
          return;
        }
        
        if (this.selectedCategory && this.selectedCategory !== '') {
          this.filteredPortfolios = this.portfolios.filter(portfolio => {
            // Skip invalid portfolio items
            if (!portfolio || typeof portfolio !== 'object') {
              console.log('Skipping invalid portfolio item');
              return false;
            }
            
            console.log('Checking portfolio:', portfolio.title, 'jenis:', portfolio.jenis, 'against category:', this.selectedCategory);
            return portfolio.jenis && (
              portfolio.jenis === this.selectedCategory || 
              (Array.isArray(portfolio.jenis) && portfolio.jenis.includes(this.selectedCategory)) ||
              (typeof portfolio.jenis === 'string' && portfolio.jenis.includes(this.selectedCategory))
            );
          });
        } else if (this.portofolioSelection && Array.isArray(this.portofolioSelection) && this.portofolioSelection.length > 0) {
          this.filteredPortfolios = this.portfolios.filter(portfolio => 
            portfolio && portfolio.jenis && this.portofolioSelection.includes(portfolio.jenis)
          );
        } else {
          this.filteredPortfolios = [...this.portfolios];
        }
        
        console.log('Filtered portfolios count:', this.filteredPortfolios.length);
        
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
        if (!Array.isArray(this.filteredPortfolios)) {
          return [];
        }
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredPortfolios.slice(start, end);
      },
      
      get totalPages() {
        if (!Array.isArray(this.filteredPortfolios)) {
          return 1;
        }
        return Math.ceil(this.filteredPortfolios.length / this.itemsPerPage);
      },
      
      getWhatsAppUrl(portfolio) {
        if (!this.whatsappNumber || !portfolio || !portfolio.title) return '#';
        const message = 'Saya tertarik dengan ' + portfolio.title;
        return 'https://wa.me/' + this.whatsappNumber + '?text=' + encodeURIComponent(message);
      },
      
      getPreviewUrl(portfolio) {
        if (!portfolio || !portfolio.id) return '#';
        return this.previewPage + '?id=' + portfolio.id;
      },
      
      getImageUrl(portfolio) {
        if (!portfolio) return '';
        
        // Debug logging
        console.log('Getting image URL for portfolio:', portfolio);
        console.log('Style thumbnail setting:', this.styleThumbnail);
        
        // Helper function to clean URL from backticks
        const cleanUrl = (url) => {
          if (!url || typeof url !== 'string') return '';
          // Remove backticks from the beginning and end of the URL
          return url.replace(/^`|`$/g, '').trim();
        };
        
        // Try to get image from _embedded data (WordPress REST API with _embed parameter)
        let imageUrl = '';
        
        if (portfolio._embedded && portfolio._embedded['wp:featuredmedia'] && portfolio._embedded['wp:featuredmedia'][0]) {
          const featuredMedia = portfolio._embedded['wp:featuredmedia'][0];
          
          // Get appropriate image size based on styleThumbnail setting
          if (this.styleThumbnail === 'thumbnail' && featuredMedia.media_details && featuredMedia.media_details.sizes && featuredMedia.media_details.sizes.thumbnail) {
            imageUrl = cleanUrl(featuredMedia.media_details.sizes.thumbnail.source_url);
          } else if (featuredMedia.media_details && featuredMedia.media_details.sizes && featuredMedia.media_details.sizes.medium) {
            imageUrl = cleanUrl(featuredMedia.media_details.sizes.medium.source_url);
          } else if (featuredMedia.media_details && featuredMedia.media_details.sizes && featuredMedia.media_details.sizes.full) {
            imageUrl = cleanUrl(featuredMedia.media_details.sizes.full.source_url);
          } else {
            // Fallback to source_url if no specific size is available
            imageUrl = cleanUrl(featuredMedia.source_url);
          }
        }
        
        // If no image found in _embedded, try other possible fields
        if (!imageUrl) {
          if (this.styleThumbnail === 'thumbnail') {
            imageUrl = cleanUrl(portfolio.thumbnail_url) || cleanUrl(portfolio.thumbnail) || cleanUrl(portfolio.image) || cleanUrl(portfolio.featured_image);
          } else {
            imageUrl = cleanUrl(portfolio.screenshot) || cleanUrl(portfolio.full_image) || cleanUrl(portfolio.image) || cleanUrl(portfolio.featured_image);
          }
          
          // Final fallback to any image field
          if (!imageUrl) {
            imageUrl = cleanUrl(portfolio.image) || cleanUrl(portfolio.featured_image) || cleanUrl(portfolio.thumbnail) || cleanUrl(portfolio.screenshot) || cleanUrl(portfolio.thumbnail_url);
          }
        }
        
        console.log('Final image URL:', imageUrl);
        return imageUrl;
      },
      
      getVisiblePages() {
        const total = this.totalPages;
        const current = this.currentPage;
        const delta = 2; // Number of pages to show on each side of the current page
        
        let range = [];
        let rangeWithDots = [];
        let l;

        for (let i = 1; i <= total; i++) {
          if (i == 1 || i == total || (i >= current - delta && i <= current + delta)) {
            range.push(i);
          }
        }

        range.forEach((i) => {
          if (l) {
            if (i - l === 2) {
              rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
              rangeWithDots.push('...');
            }
          }
          rangeWithDots.push(i);
          l = i;
        });

        return rangeWithDots;
      }
    }));
  });

  // Fallback for browsers without Alpine.js and to handle double-click issue
  document.addEventListener("DOMContentLoaded", function () {
    // Get the modal elements
    const modalTrigger = document.querySelector(".btn-modal-portofolio");
    const modal = document.querySelector(".frame-modal-portofolio");
    const closeModalBtn = document.querySelector(".close-modal-portofolio");

    if (modalTrigger && modal && closeModalBtn) {
      // Function to open the modal
      function openModal(event) {
        event.preventDefault();
        console.log('Opening modal via fallback');
        
        // Force display with !important to override any CSS
        modal.style.setProperty('display', 'block', 'important');
        modal.style.setProperty('opacity', '1', 'important');
        modal.style.setProperty('visibility', 'visible', 'important');
        modal.style.setProperty('z-index', '9999', 'important');
        
        // Add a custom class to ensure modal is visible
        modal.classList.add('modal-force-show');
        
        // Try multiple approaches to set modalOpen to true if Alpine is available
        if (window.Alpine) {
          // Try to access Alpine data directly
          setTimeout(() => {
            try {
              // Method 1: Try to find the component by x-data attribute (most reliable)
              const alpineComponent = modal.closest('[x-data]');
              if (alpineComponent) {
                const componentData = Alpine.$data(alpineComponent);
                if (componentData && componentData.modalOpen !== undefined) {
                  componentData.modalOpen = true;
                  console.log('Set modalOpen via closest x-data element');
                }
              }
              
              // Method 2: Try to get Alpine data from parent element
              const modalComponent = Alpine.$data(modal.parentElement);
              if (modalComponent && modalComponent.modalOpen !== undefined) {
                modalComponent.modalOpen = true;
                console.log('Set modalOpen via Alpine.$data');
              }
              
              // Method 3: Try to evaluate Alpine expression
              if (Alpine.evaluate) {
                Alpine.evaluate(modal, 'modalOpen = true');
                console.log('Set modalOpen via Alpine.evaluate');
              }
            } catch (e) {
              console.error('Error setting modalOpen via Alpine:', e);
            }
          }, 100);
        }
      }

      // Function to close the modal
      function closeModal(event) {
        event.preventDefault();
        event.stopPropagation();
        console.log('Closing modal via fallback');
        
        // Force hide with !important to override any CSS
        modal.style.setProperty('display', 'none', 'important');
        modal.style.setProperty('opacity', '0', 'important');
        modal.style.setProperty('visibility', 'hidden', 'important');
        
        // Remove the custom class
        modal.classList.remove('modal-force-show');
        
        // Try multiple approaches to set modalOpen to false if Alpine is available
        if (window.Alpine) {
          setTimeout(() => {
            try {
              // Method 1: Try to find the component by x-data attribute (most reliable)
              const alpineComponent = modal.closest('[x-data]');
              if (alpineComponent) {
                const componentData = Alpine.$data(alpineComponent);
                if (componentData && componentData.modalOpen !== undefined) {
                  componentData.modalOpen = false;
                  console.log('Set modalOpen to false via closest x-data element');
                }
              }
              
              // Method 2: Try to get Alpine data from parent element
              const modalComponent = Alpine.$data(modal.parentElement);
              if (modalComponent && modalComponent.modalOpen !== undefined) {
                modalComponent.modalOpen = false;
                console.log('Set modalOpen to false via Alpine.$data');
              }
              
              // Method 3: Try to evaluate Alpine expression
              if (Alpine.evaluate) {
                Alpine.evaluate(modal, 'modalOpen = false');
                console.log('Set modalOpen to false via Alpine.evaluate');
              }
            } catch (e) {
              console.error('Error setting modalOpen to false via Alpine:', e);
            }
          }, 100);
        }
      }

      // Event listener to open the modal when button is clicked
      modalTrigger.addEventListener("click", openModal);

      // Event listener to close the modal when close button is clicked
      closeModalBtn.addEventListener("click", closeModal);
      
      // Event listeners for category items
      const categoryItems = document.querySelectorAll(".list-portofolio");
      categoryItems.forEach(item => {
        item.addEventListener("click", function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Get category from the element
          const categoryText = this.querySelector('.fw-bold').textContent;
          console.log('Category clicked via fallback:', categoryText);
          
          // Find matching category from data
          const categoriesData = document.querySelector('#categories-data');
          if (categoriesData) {
            try {
              const categories = JSON.parse(categoriesData.textContent) || [];
              const category = categories.find(cat => cat.category === categoryText);
              if (category) {
                const categorySlug = category.slug || category.category;
                
                // Update URL
                const url = new URL(window.location);
                url.searchParams.set('jenis_web', categorySlug);
                url.searchParams.delete('halaman'); // Reset to page 1
                window.history.pushState({}, '', url);
                
                // Close modal
                closeModal(e);
                
                // Reload page
                setTimeout(() => {
                  window.location.reload();
                }, 100);
              }
            } catch (e) {
              console.error('Error parsing categories data:', e);
            }
          }
        });
      });
      
      // Close modal when clicking outside
      modal.addEventListener("click", function(e) {
        if (e.target === modal) {
          closeModal(e);
        }
      });

      // Event listener to close the modal when clicking outside of the modal content
      window.addEventListener("click", function (event) {
        if (event.target === modal) {
          closeModal();
        }
      });
    }
    
    // Additional fallback: Add event listener after Alpine.js is loaded
    if (window.Alpine) {
      // If Alpine is already loaded, add the listener immediately
      setTimeout(() => {
        const trigger = document.querySelector(".btn-modal-portofolio");
        if (trigger) {
          console.log('Adding additional click listener for Alpine.js compatibility');
          trigger.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Modal button clicked via additional listener');
            
            const modal = document.querySelector(".frame-modal-portofolio");
            if (modal) {
              modal.style.setProperty('display', 'block', 'important');
              modal.style.setProperty('opacity', '1', 'important');
              modal.style.setProperty('visibility', 'visible', 'important');
              modal.style.setProperty('z-index', '9999', 'important');
              modal.classList.add('modal-force-show');
              
              // Try to set Alpine state
              const alpineComponent = modal.closest('[x-data]');
              if (alpineComponent && window.Alpine) {
                try {
                  const componentData = Alpine.$data(alpineComponent);
                  if (componentData && componentData.modalOpen !== undefined) {
                    componentData.modalOpen = true;
                    console.log('Set modalOpen via additional listener');
                  }
                } catch (e) {
                  console.error('Error setting modalOpen via additional listener:', e);
                }
              }
            }
          });
        }
      }, 500);
    } else {
      // If Alpine is not loaded yet, wait for it
      document.addEventListener('alpine:init', function() {
        setTimeout(() => {
          const trigger = document.querySelector(".btn-modal-portofolio");
          if (trigger) {
            console.log('Adding additional click listener after Alpine.js init');
            trigger.addEventListener("click", function(e) {
              e.preventDefault();
              e.stopPropagation();
              console.log('Modal button clicked via post-init listener');
              
              const modal = document.querySelector(".frame-modal-portofolio");
              if (modal) {
                modal.style.setProperty('display', 'block', 'important');
                modal.style.setProperty('opacity', '1', 'important');
                modal.style.setProperty('visibility', 'visible', 'important');
                modal.style.setProperty('z-index', '9999', 'important');
                modal.classList.add('modal-force-show');
                
                // Try to set Alpine state
                const alpineComponent = modal.closest('[x-data]');
                if (alpineComponent && window.Alpine) {
                  try {
                    const componentData = Alpine.$data(alpineComponent);
                    if (componentData && componentData.modalOpen !== undefined) {
                      componentData.modalOpen = true;
                      console.log('Set modalOpen via post-init listener');
                    }
                  } catch (e) {
                    console.error('Error setting modalOpen via post-init listener:', e);
                  }
                }
              }
            });
          }
        }, 500);
      });
    }
  });

}));
//# sourceMappingURL=script.js.map
