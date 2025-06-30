/**
 * Script de recherche pour ElvyMade
 * Fonctionnalités de recherche et filtrage
 */

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les fonctionnalités de recherche
  initSearchForm()
  initFilters()
  initSorting()
  initPriceRange()
})

/**
 * Initialise le formulaire de recherche
 */
function initSearchForm() {
  const searchForm = document.querySelector(".search-form")
  const searchInput = document.querySelector(".search-input")

  if (searchForm && searchInput) {
    // Ajouter l'autocomplétion
    searchInput.addEventListener(
      "input",
      debounce(() => {
        const query = searchInput.value.trim()

        if (query.length >= 2) {
          fetchSearchSuggestions(query)
        } else {
          // Cacher les suggestions si la requête est trop courte
          const suggestionsContainer = document.querySelector(".search-suggestions")
          if (suggestionsContainer) {
            suggestionsContainer.innerHTML = ""
            suggestionsContainer.style.display = "none"
          }
        }
      }, 300),
    )

    // Gérer la soumission du formulaire
    searchForm.addEventListener("submit", (e) => {
      const query = searchInput.value.trim()

      if (query.length === 0) {
        e.preventDefault()
        alert("Veuillez entrer un terme de recherche") // showToast('Veuillez entrer un terme de recherche', 'warning');
      }
    })
  }
}

/**
 * Récupère les suggestions de recherche via AJAX
 * @param {string} query - Terme de recherche
 */
function fetchSearchSuggestions(query) {
  fetch("api/search.php?q=" + encodeURIComponent(query) + "&suggest=1")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.suggestions.length > 0) {
        displaySearchSuggestions(data.suggestions)
      } else {
        // Cacher les suggestions s'il n'y en a pas
        const suggestionsContainer = document.querySelector(".search-suggestions")
        if (suggestionsContainer) {
          suggestionsContainer.innerHTML = ""
          suggestionsContainer.style.display = "none"
        }
      }
    })
    .catch((error) => {
      console.error("Erreur:", error)
    })
}

/**
 * Affiche les suggestions de recherche
 * @param {Array} suggestions - Liste des suggestions
 */
function displaySearchSuggestions(suggestions) {
  let suggestionsContainer = document.querySelector(".search-suggestions")

  // Créer le conteneur s'il n'existe pas
  if (!suggestionsContainer) {
    suggestionsContainer = document.createElement("div")
    suggestionsContainer.className = "search-suggestions"

    const searchForm = document.querySelector(".search-form")
    if (searchForm) {
      searchForm.appendChild(suggestionsContainer)
    }
  }

  // Vider et remplir le conteneur
  suggestionsContainer.innerHTML = ""
  suggestionsContainer.style.display = "block"

  suggestions.forEach((suggestion) => {
    const item = document.createElement("div")
    item.className = "suggestion-item"

    // Créer le contenu de la suggestion
    if (suggestion.image) {
      const img = document.createElement("img")
      img.src = suggestion.image
      img.alt = suggestion.name
      img.className = "suggestion-image"
      item.appendChild(img)
    }

    const content = document.createElement("div")
    content.className = "suggestion-content"

    const name = document.createElement("div")
    name.className = "suggestion-name"
    name.textContent = suggestion.name
    content.appendChild(name)

    if (suggestion.price) {
      const price = document.createElement("div")
      price.className = "suggestion-price"
      price.textContent = suggestion.price + " FCFA"
      content.appendChild(price)
    }

    item.appendChild(content)

    // Ajouter l'événement de clic
    item.addEventListener("click", () => {
      window.location.href = "pages/product.php?id=" + suggestion.id
    })

    suggestionsContainer.appendChild(item)
  })

  // Fermer les suggestions en cliquant en dehors
  document.addEventListener("click", function closeDropdown(event) {
    const searchForm = document.querySelector(".search-form")
    const suggestionsContainer = document.querySelector(".search-suggestions")

    if (suggestionsContainer && !suggestionsContainer.contains(event.target) && !searchForm.contains(event.target)) {
      suggestionsContainer.style.display = "none"
      document.removeEventListener("click", closeDropdown)
    }
  })
}

/**
 * Initialise les filtres de recherche
 */
function initFilters() {
  const filterForm = document.querySelector(".filter-form")
  const applyFiltersBtn = document.querySelector(".apply-filters-btn")
  const resetFiltersBtn = document.querySelector(".reset-filters-btn")
  const categoryCheckboxes = document.querySelectorAll(".category-checkbox")

  if (filterForm) {
    // Appliquer les filtres
    if (applyFiltersBtn) {
      applyFiltersBtn.addEventListener("click", (e) => {
        e.preventDefault()
        applyFilters()
      })
    }

    // Réinitialiser les filtres
    if (resetFiltersBtn) {
      resetFiltersBtn.addEventListener("click", (e) => {
        e.preventDefault()
        resetFilters()
      })
    }

    // Filtrer automatiquement lors du changement de catégorie
    if (categoryCheckboxes.length > 0) {
      categoryCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", () => {
          // Si on est sur mobile, ne pas filtrer automatiquement
          if (window.innerWidth > 768) {
            applyFilters()
          }
        })
      })
    }
  }
}

/**
 * Applique les filtres de recherche
 */
function applyFilters() {
  const filterForm = document.querySelector(".filter-form")

  if (filterForm) {
    // Récupérer les valeurs des filtres
    const formData = new FormData(filterForm)
    const params = new URLSearchParams(formData)

    // Récupérer les paramètres de l'URL actuelle
    const currentParams = new URLSearchParams(window.location.search)
    const query = currentParams.get("q") || ""

    // Ajouter le terme de recherche s'il existe
    if (query) {
      params.set("q", query)
    }

    // Rediriger vers la page de recherche avec les filtres
    window.location.href = "pages/search.php?" + params.toString()
  }
}

/**
 * Réinitialise les filtres de recherche
 */
function resetFilters() {
  const filterForm = document.querySelector(".filter-form")
  const priceMin = document.querySelector("#price-min")
  const priceMax = document.querySelector("#price-max")
  const categoryCheckboxes = document.querySelectorAll(".category-checkbox")

  if (filterForm) {
    filterForm.reset()

    // Réinitialiser les sliders de prix
    if (priceMin && priceMax) {
      priceMin.value = priceMin.getAttribute("min") || 0
      priceMax.value = priceMax.getAttribute("max") || 1000000

      // Mettre à jour l'affichage des valeurs
      const priceMinDisplay = document.querySelector(".price-min-display")
      const priceMaxDisplay = document.querySelector(".price-max-display")

      if (priceMinDisplay) {
        priceMinDisplay.textContent = formatPrice(priceMin.value)
      }

      if (priceMaxDisplay) {
        priceMaxDisplay.textContent = formatPrice(priceMax.value)
      }
    }

    // Décocher toutes les catégories
    if (categoryCheckboxes.length > 0) {
      categoryCheckboxes.forEach((checkbox) => {
        checkbox.checked = false
      })
    }

    // Récupérer les paramètres de l'URL actuelle
    const currentParams = new URLSearchParams(window.location.search)
    const query = currentParams.get("q") || ""

    // Rediriger vers la page de recherche avec uniquement le terme de recherche
    if (query) {
      window.location.href = "pages/search.php?q=" + encodeURIComponent(query)
    } else {
      window.location.href = "pages/search.php"
    }
  }
}

/**
 * Initialise le tri des résultats
 */
function initSorting() {
  const sortSelect = document.querySelector(".sort-select")

  if (sortSelect) {
    sortSelect.addEventListener("change", () => {
      // Récupérer les paramètres de l'URL actuelle
      const currentParams = new URLSearchParams(window.location.search)

      // Mettre à jour le paramètre de tri
      currentParams.set("sort", sortSelect.value)

      // Rediriger vers la page avec le nouveau tri
      window.location.href = "pages/search.php?" + currentParams.toString()
    })
  }
}

/**
 * Initialise les sliders de plage de prix
 */
function initPriceRange() {
  const priceMin = document.querySelector("#price-min")
  const priceMax = document.querySelector("#price-max")
  const priceMinDisplay = document.querySelector(".price-min-display")
  const priceMaxDisplay = document.querySelector(".price-max-display")

  if (priceMin && priceMax && priceMinDisplay && priceMaxDisplay) {
    // Mettre à jour l'affichage initial
    priceMinDisplay.textContent = formatPrice(priceMin.value)
    priceMaxDisplay.textContent = formatPrice(priceMax.value)

    // Mettre à jour lors du changement
    priceMin.addEventListener("input", () => {
      priceMinDisplay.textContent = formatPrice(priceMin.value)

      // Assurer que min <= max
      if (Number.parseInt(priceMin.value) > Number.parseInt(priceMax.value)) {
        priceMax.value = priceMin.value
        priceMaxDisplay.textContent = formatPrice(priceMax.value)
      }
    })

    priceMax.addEventListener("input", () => {
      priceMaxDisplay.textContent = formatPrice(priceMax.value)

      // Assurer que max >= min
      if (Number.parseInt(priceMax.value) < Number.parseInt(priceMin.value)) {
        priceMin.value = priceMax.value
        priceMinDisplay.textContent = formatPrice(priceMin.value)
      }
    })
  }
}

/**
 * Formate un prix en FCFA
 * @param {number|string} price - Prix à formater
 * @return {string} - Prix formaté
 */
function formatPrice(price) {
  return new Intl.NumberFormat("fr-FR").format(price) + " FCFA"
}

/**
 * Fonction debounce pour limiter les appels fréquents
 * @param {Function} func - Fonction à exécuter
 * @param {number} wait - Délai d'attente en ms
 * @return {Function} - Fonction avec debounce
 */
function debounce(func, wait) {
  let timeout

  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }

    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}
