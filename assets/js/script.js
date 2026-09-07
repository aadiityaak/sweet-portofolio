/*!
 * Sweetweb v1.1.7 (https://websweetstudio.com)
 * Copyright 2013-2026 websweetstudio.com
 * Licensed under GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
 *
 * Vanilla JS — no external dependencies (Alpine.js removed).
 */
(function () {
  "use strict";

  var PLACEHOLDER_IMG =
    "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIE5vdCBBdmFpbGFibGU8L3RleHQ+PC9zdmc+";

  function parseJSONScript(id) {
    var el = document.getElementById(id);
    if (!el) return null;
    try {
      return JSON.parse(el.textContent);
    } catch (e) {
      console.error("Sweet Portofolio: invalid JSON in #" + id, e);
      return null;
    }
  }

  function cleanUrl(url) {
    if (!url || typeof url !== "string") return "";
    return url.replace(/^`|`$/g, "").trim();
  }

  function esc(str) {
    var d = document.createElement("div");
    d.textContent = str == null ? "" : String(str);
    return d.innerHTML;
  }

  function matchesCategory(portfolio, category) {
    if (!portfolio || !portfolio.jenis || !category) return false;
    if (Array.isArray(portfolio.jenis))
      return portfolio.jenis.indexOf(category) !== -1;
    if (typeof portfolio.jenis === "string")
      return portfolio.jenis.indexOf(category) !== -1;
    return false;
  }

  function getVisiblePages(total, current) {
    var delta = 2;
    var range = [];
    var rangeWithDots = [];
    var l;
    var i;

    for (i = 1; i <= total; i++) {
      if (
        i === 1 ||
        i === total ||
        (i >= current - delta && i <= current + delta)
      ) {
        range.push(i);
      }
    }

    range.forEach(function (n) {
      if (l) {
        if (n - l === 2) {
          rangeWithDots.push(l + 1);
        } else if (n - l !== 1) {
          rangeWithDots.push("...");
        }
      }
      rangeWithDots.push(n);
      l = n;
    });

    return rangeWithDots;
  }

  function PortfolioGrid(root) {
    this.root = root;

    var cfg = {};
    try {
      cfg = JSON.parse(root.getAttribute("data-config")) || {};
    } catch (e) {
      cfg = {};
    }

    this.itemsPerPage = 12;
    this.portfolios = [];
    this.filteredPortfolios = [];
    this.selectedCategory = cfg.initialCategory || "";
    this.currentPage = cfg.initialPage || 1;
    this.showTitle = cfg.showTitle !== "no";
    this.showDescription = !!cfg.showDescription;
    this.styleThumbnail = cfg.styleThumbnail || "";
    this.previewPage = cfg.previewPage || "";
    this.whatsappNumber = cfg.whatsappNumber || "";
    this.portofolioCredit = cfg.portofolioCredit || "";
    this.portofolioSelection = Array.isArray(cfg.portofolioSelection)
      ? cfg.portofolioSelection
      : [];
    this.selectedIds = Array.isArray(cfg.selectedIds) ? cfg.selectedIds : [];

    this.grid = root.querySelector("#portfolio-grid");
    this.emptyState = root.querySelector(".portfolio-empty-state");
    this.paginationEl = root.querySelector(".pagination");
    this.paginationPages = root.querySelector(".pagination-pages");
    this.paginationInfo = root.querySelector(".pagination-info");
    this.prevBtn = root.querySelector('[data-pagination="prev"]');
    this.nextBtn = root.querySelector('[data-pagination="next"]');
    this.resultsKicker = root.querySelector(".portfolio-results-kicker");
    this.resultsSummary = root.querySelector(".portfolio-results-summary");

    // Category select filter
    var categoryFilter = root.querySelector("#category-filter");
    if (categoryFilter) {
      categoryFilter.value = this.selectedCategory;
      var self = this;
      categoryFilter.addEventListener("change", function () {
        self.selectedCategory = this.value;
        self.currentPage = 1;
        self.updateURL();
        self.filter();
        self.render();
      });
      this.categoryFilter = categoryFilter;
    }

    var rawData = parseJSONScript("portfolios-data");
    if (Array.isArray(rawData)) {
      if (
        rawData.length === 1 &&
        rawData[0] &&
        rawData[0].code === "rest_forbidden"
      ) {
        console.error(
          "Sweet Portofolio: API returned forbidden error:",
          rawData[0].message,
        );
        this.portfolios = [];
      } else {
        this.portfolios = rawData;
      }
    }

    // URL overrides
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("jenis_web")) {
      this.selectedCategory = urlParams.get("jenis_web");
      if (this.categoryFilter)
        this.categoryFilter.value = this.selectedCategory;
    }
    if (urlParams.has("halaman")) {
      this.currentPage = parseInt(urlParams.get("halaman"), 10) || 1;
    }

    var self2 = this;
    window.addEventListener("popstate", function () {
      self2.updateFromURL();
    });

    this.filter();
    this.render();
  }

  PortfolioGrid.prototype.updateFromURL = function () {
    var urlParams = new URLSearchParams(window.location.search);
    this.selectedCategory = urlParams.get("jenis_web") || "";
    this.currentPage = parseInt(urlParams.get("halaman"), 10) || 1;
    if (this.categoryFilter) this.categoryFilter.value = this.selectedCategory;
    this.filter();
    this.render();
  };

  PortfolioGrid.prototype.updateURL = function () {
    var url = new URL(window.location);
    if (this.selectedCategory && this.selectedCategory !== "") {
      url.searchParams.set("jenis_web", this.selectedCategory);
    } else {
      url.searchParams.delete("jenis_web");
    }
    if (this.currentPage > 1) {
      url.searchParams.set("halaman", this.currentPage);
    } else {
      url.searchParams.delete("halaman");
    }
    window.history.pushState({}, "", url);
  };

  PortfolioGrid.prototype.filter = function () {
    var portfolios = Array.isArray(this.portfolios) ? this.portfolios : [];
    var category = this.selectedCategory;

    if (portfolios.length === 0) {
      this.filteredPortfolios = [];
      return;
    }

    if (Array.isArray(this.selectedIds) && this.selectedIds.length > 0) {
      var idSet = {};
      this.selectedIds.forEach(function (v) {
        idSet[parseInt(v, 10)] = true;
      });
      this.filteredPortfolios = portfolios.filter(function (p) {
        return (
          p && p.id !== undefined && p.id !== null && idSet[parseInt(p.id, 10)]
        );
      });
      if (category) {
        this.filteredPortfolios = this.filteredPortfolios.filter(function (p) {
          return matchesCategory(p, category);
        });
      }
    } else if (category) {
      this.filteredPortfolios = portfolios.filter(function (p) {
        return matchesCategory(p, category);
      });
    } else if (this.portofolioSelection.length > 0) {
      var selection = this.portofolioSelection;
      this.filteredPortfolios = portfolios.filter(function (p) {
        if (!p) return false;
        if (Array.isArray(p.jenis)) {
          for (var i = 0; i < p.jenis.length; i++) {
            if (selection.indexOf(p.jenis[i]) !== -1) return true;
          }
        } else if (typeof p.jenis === "string") {
          if (selection.indexOf(p.jenis) !== -1) return true;
        }
        if (p.jenis_web && selection.indexOf(p.jenis_web) !== -1) return true;
        return false;
      });
    } else {
      this.filteredPortfolios = portfolios.slice();
    }
  };

  PortfolioGrid.prototype.getImageUrl = function (portfolio) {
    if (!portfolio) return "";

    var imageUrl = "";
    var styleThumbnail = this.styleThumbnail;

    if (
      portfolio._embedded &&
      portfolio._embedded["wp:featuredmedia"] &&
      portfolio._embedded["wp:featuredmedia"][0]
    ) {
      var featuredMedia = portfolio._embedded["wp:featuredmedia"][0];
      var sizes =
        featuredMedia.media_details && featuredMedia.media_details.sizes;

      if (styleThumbnail === "thumbnail" && sizes && sizes.thumbnail) {
        imageUrl = cleanUrl(sizes.thumbnail.source_url);
      } else if (sizes && sizes.medium) {
        imageUrl = cleanUrl(sizes.medium.source_url);
      } else if (sizes && sizes.full) {
        imageUrl = cleanUrl(sizes.full.source_url);
      } else {
        imageUrl = cleanUrl(featuredMedia.source_url);
      }
    }

    if (!imageUrl) {
      if (styleThumbnail === "thumbnail") {
        imageUrl =
          cleanUrl(portfolio.thumbnail_url) ||
          cleanUrl(portfolio.thumbnail) ||
          cleanUrl(portfolio.image) ||
          cleanUrl(portfolio.featured_image);
      } else {
        imageUrl =
          cleanUrl(portfolio.screenshot) ||
          cleanUrl(portfolio.full_image) ||
          cleanUrl(portfolio.image) ||
          cleanUrl(portfolio.featured_image);
      }

      if (!imageUrl) {
        imageUrl =
          cleanUrl(portfolio.image) ||
          cleanUrl(portfolio.featured_image) ||
          cleanUrl(portfolio.thumbnail) ||
          cleanUrl(portfolio.screenshot) ||
          cleanUrl(portfolio.thumbnail_url);
      }
    }

    return imageUrl;
  };

  PortfolioGrid.prototype.getPreviewUrl = function (portfolio) {
    if (!portfolio || !portfolio.id || !this.previewPage) return "#";
    return this.previewPage + "?id=" + portfolio.id;
  };

  PortfolioGrid.prototype.getWhatsAppUrl = function (portfolio) {
    if (!this.whatsappNumber || !portfolio || !portfolio.title) return "#";
    var message = "Saya tertarik dengan " + portfolio.title;
    return (
      "https://wa.me/" +
      this.whatsappNumber +
      "?text=" +
      encodeURIComponent(message)
    );
  };

  PortfolioGrid.prototype.totalPages = function () {
    if (!Array.isArray(this.filteredPortfolios)) return 1;
    return Math.ceil(this.filteredPortfolios.length / this.itemsPerPage);
  };

  PortfolioGrid.prototype.paginated = function () {
    if (!Array.isArray(this.filteredPortfolios)) return [];
    var start = (this.currentPage - 1) * this.itemsPerPage;
    return this.filteredPortfolios.slice(start, start + this.itemsPerPage);
  };

  PortfolioGrid.prototype.renderCard = function (item) {
    var credit = this.portofolioCredit;
    var showTitle = this.showTitle;
    var showDescription = this.showDescription;
    var previewUrl = esc(this.getPreviewUrl(item));
    var waUrl = esc(this.getWhatsAppUrl(item));

    var html = '<div class="col-portofolio"><div class="card-portofolio">';
    html += '<div class="card-image">';
    html +=
      '<img src="' +
      esc(this.getImageUrl(item) || PLACEHOLDER_IMG) +
      '" alt="' +
      esc(item.title) +
      '" loading="lazy">';
    if (credit) {
      html += '<span class="card-credit">' + esc(credit) + "</span>";
    }
    html += '<div class="card-actions">';
    html +=
      '<a href="' +
      previewUrl +
      '" class="btn-preview" target="_blank" rel="noopener">' +
      '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">' +
      '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>' +
      '<path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>' +
      "</svg> Preview</a>";
    if (this.whatsappNumber) {
      html +=
        '<a href="' +
        waUrl +
        '" class="btn-whatsapp" target="_blank" rel="noopener">' +
        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">' +
        '<path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>' +
        "</svg> Order</a>";
    }
    html += "</div></div>"; // card-actions, card-image

    html += '<div class="card-content"><div class="card-meta-row">';
    html += '<span class="card-meta-pill">Website Portfolio</span>';
    if (credit) {
      html += '<span class="card-meta-text">' + esc(credit) + "</span>";
    }
    html += "</div>"; // card-meta-row

    if (showTitle) {
      html +=
        '<h3 class="card-title"><a href="' +
        previewUrl +
        '" class="card-title-link">' +
        esc(item.title) +
        "</a></h3>";
    }

    if (showDescription && item.excerpt) {
      // Excerpt comes from trusted API; kept as HTML like the previous x-html behavior
      html += '<p class="card-excerpt">' + item.excerpt + "</p>";
    }

    html += "</div></div></div>"; // card-content, card-portofolio, col-portofolio
    return html;
  };

  PortfolioGrid.prototype.render = function () {
    var self = this;
    var total = this.totalPages();
    var items = this.paginated();
    var count = this.filteredPortfolios.length;

    // Results bar
    if (this.resultsKicker) {
      this.resultsKicker.textContent =
        this.selectedCategory || "Semua kategori";
    }
    if (this.resultsSummary) {
      this.resultsSummary.textContent = count + " item tersedia";
    }

    // Empty state / grid
    if (this.emptyState) {
      this.emptyState.style.display = count === 0 ? "" : "none";
    }
    if (this.grid) {
      this.grid.style.display = count > 0 ? "" : "none";
      this.grid.innerHTML = items
        .map(function (item) {
          return self.renderCard(item);
        })
        .join("");
    }

    // Pagination visibility
    var showPagination = total > 1;
    if (this.paginationEl) {
      this.paginationEl.style.display = showPagination ? "" : "none";
    }
    if (this.paginationInfo) {
      this.paginationInfo.style.display = showPagination ? "" : "none";
      if (showPagination) {
        var from = (this.currentPage - 1) * this.itemsPerPage + 1;
        var to = Math.min(this.currentPage * this.itemsPerPage, count);
        this.paginationInfo.querySelector("span").textContent =
          from + "-" + to + " dari " + count + " items";
      }
    }

    if (showPagination && this.paginationPages) {
      var html = "";
      getVisiblePages(total, this.currentPage).forEach(function (page) {
        if (page === "...") {
          html +=
            '<span class="pagination-btn disabled" aria-disabled="true">...</span>';
        } else {
          html +=
            '<span class="pagination-btn' +
            (page === self.currentPage ? " active" : "") +
            '" role="button" tabindex="0" data-page="' +
            page +
            '">' +
            page +
            "</span>";
        }
      });
      this.paginationPages.innerHTML = html;

      Array.prototype.forEach.call(
        this.paginationPages.querySelectorAll("[data-page]"),
        function (btn) {
          btn.addEventListener("click", function () {
            self.goToPage(parseInt(this.getAttribute("data-page"), 10));
          });
          btn.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              self.goToPage(parseInt(this.getAttribute("data-page"), 10));
            }
          });
        },
      );
    }

    // Prev / next
    if (this.prevBtn) {
      this.prevBtn.classList.toggle("disabled", this.currentPage === 1);
      this.prevBtn.setAttribute(
        "tabindex",
        this.currentPage === 1 ? "-1" : "0",
      );
    }
    if (this.nextBtn) {
      this.nextBtn.classList.toggle("disabled", this.currentPage === total);
      this.nextBtn.setAttribute(
        "tabindex",
        this.currentPage === total ? "-1" : "0",
      );
    }
  };

  PortfolioGrid.prototype.goToPage = function (page) {
    var total = this.totalPages();
    if (page < 1 || page > total || page === this.currentPage) return;
    this.currentPage = page;
    this.updateURL();
    this.render();
    // Scroll back to the top of the grid for pagination navigation
    if (this.grid) {
      this.grid.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  };

  // ------------------------------------------------------------------
  // Category modal (legacy template block)
  // ------------------------------------------------------------------
  function initCategoryModal() {
    var modalTrigger = document.querySelector(".btn-modal-portofolio");
    var modal = document.querySelector(".frame-modal-portofolio");
    var closeModalBtn = document.querySelector(".close-modal-portofolio");

    if (!modalTrigger || !modal || !closeModalBtn) return;

    function openModal(e) {
      if (e) e.preventDefault();
      modal.classList.add("modal-force-show");
      modal.style.setProperty("display", "block", "important");
      modal.style.setProperty("opacity", "1", "important");
      modal.style.setProperty("visibility", "visible", "important");
      modal.style.setProperty("z-index", "9999", "important");
    }

    function closeModal(e) {
      if (e) e.preventDefault();
      modal.classList.remove("modal-force-show");
      modal.style.setProperty("display", "none", "important");
      modal.style.setProperty("opacity", "0", "important");
      modal.style.setProperty("visibility", "hidden", "important");
    }

    modalTrigger.addEventListener("click", openModal);
    closeModalBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", function (e) {
      if (e.target === modal) closeModal(e);
    });

    // Category selection inside modal
    var categories = parseJSONScript("categories-data") || [];
    var categoryItems = document.querySelectorAll(".list-portofolio");
    Array.prototype.forEach.call(categoryItems, function (item) {
      item.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var categoryText = item.querySelector(".fw-bold");
        if (!categoryText) return;

        var match = null;
        for (var i = 0; i < categories.length; i++) {
          if (categories[i].category === categoryText.textContent) {
            match = categories[i];
            break;
          }
        }
        if (!match) return;

        var slug = match.slug || match.category;
        var url = new URL(window.location);
        url.searchParams.set("jenis_web", slug);
        url.searchParams.delete("halaman");
        window.history.pushState({}, "", url);
        closeModal(e);
        window.location.reload();
      });
    });
  }

  // ------------------------------------------------------------------
  // Bootstrap
  // ------------------------------------------------------------------
  function init() {
    var roots = document.querySelectorAll(".portfolio-shell");
    Array.prototype.forEach.call(roots, function (root) {
      if (root.__sweetPortofolioGrid) return;
      root.__sweetPortofolioGrid = new PortfolioGrid(root);
    });
    initCategoryModal();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
