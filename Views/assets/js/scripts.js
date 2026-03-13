/*!
* Start Bootstrap - Shop Homepage v5.0.6 (https://startbootstrap.com/template/shop-homepage)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-shop-homepage/blob/master/LICENSE)
*/

document.addEventListener("DOMContentLoaded", function () {
  var searchInput = document.getElementById("productSearchInput");
  var productGrid = document.getElementById("productsGrid");

  if (!searchInput || !productGrid) {
    return;
  }

  var productCards = productGrid.querySelectorAll(".col.mb-5");

  searchInput.addEventListener("input", function () {
    var query = searchInput.value.trim().toLowerCase();

    productCards.forEach(function (cardCol) {
      var titleEl = cardCol.querySelector(".fw-bolder");
      var title = titleEl ? titleEl.textContent.toLowerCase() : "";
      var matches = title.indexOf(query) !== -1;

      cardCol.style.display = matches ? "" : "none";
    });
  });
});
