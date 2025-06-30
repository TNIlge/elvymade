import { Chart } from "@/components/ui/chart"
/**
 * Script d'administration pour ElvyMade
 * Fonctionnalités du panneau d'administration
 */

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les fonctionnalités d'administration
  initSidebar()
  initDataTables()
  initFormValidation()
  initImagePreview()
  initDeleteConfirmation()
  initCharts()
})

/**
 * Initialise la sidebar d'administration
 */
function initSidebar() {
  const sidebarToggle = document.querySelector(".sidebar-toggle")
  const adminSidebar = document.querySelector(".admin-sidebar")
  const adminMain = document.querySelector(".admin-main")

  if (sidebarToggle && adminSidebar && adminMain) {
    // Vérifier l'état enregistré
    const sidebarState = localStorage.getItem("admin-sidebar-state")
    if (sidebarState === "collapsed") {
      adminSidebar.classList.add("collapsed")
      adminMain.classList.add("expanded")
    }

    // Ajouter l'événement de clic
    sidebarToggle.addEventListener("click", () => {
      adminSidebar.classList.toggle("collapsed")
      adminMain.classList.toggle("expanded")

      // Enregistrer l'état
      if (adminSidebar.classList.contains("collapsed")) {
        localStorage.setItem("admin-sidebar-state", "collapsed")
      } else {
        localStorage.setItem("admin-sidebar-state", "expanded")
      }
    })

    // Gestion mobile
    const mobileToggle = document.querySelector(".mobile-sidebar-toggle")
    if (mobileToggle) {
      mobileToggle.addEventListener("click", () => {
        adminSidebar.classList.toggle("mobile-open")
        document.body.classList.toggle("sidebar-open")
      })

      // Fermer en cliquant en dehors
      document.addEventListener("click", (e) => {
        if (
          window.innerWidth <= 768 &&
          adminSidebar.classList.contains("mobile-open") &&
          !adminSidebar.contains(e.target) &&
          !mobileToggle.contains(e.target)
        ) {
          adminSidebar.classList.remove("mobile-open")
          document.body.classList.remove("sidebar-open")
        }
      })
    }
  }
}

/**
 * Initialise les tableaux de données
 */
function initDataTables() {
  const tables = document.querySelectorAll(".admin-table")

  if (tables.length > 0) {
    tables.forEach((table) => {
      // Ajouter la recherche
      const tableContainer = table.closest(".admin-table-container")
      if (tableContainer) {
        const searchInput = tableContainer.querySelector(".table-search")

        if (searchInput) {
          searchInput.addEventListener("input", function () {
            const searchTerm = this.value.toLowerCase()
            const rows = table.querySelectorAll("tbody tr")

            rows.forEach((row) => {
              const text = row.textContent.toLowerCase()
              if (text.includes(searchTerm)) {
                row.style.display = ""
              } else {
                row.style.display = "none"
              }
            })
          })
        }
      }

      // Ajouter le tri
      const headers = table.querySelectorAll("th[data-sort]")

      headers.forEach((header) => {
        header.addEventListener("click", function () {
          const sortKey = this.getAttribute("data-sort")
          const sortDirection = this.getAttribute("data-direction") || "asc"

          // Réinitialiser les autres en-têtes
          headers.forEach((h) => {
            if (h !== header) {
              h.removeAttribute("data-direction")
              h.classList.remove("sort-asc", "sort-desc")
            }
          })

          // Trier le tableau
          sortTable(table, sortKey, sortDirection)

          // Mettre à jour la direction
          const newDirection = sortDirection === "asc" ? "desc" : "asc"
          this.setAttribute("data-direction", newDirection)

          // Mettre à jour les classes
          this.classList.remove("sort-asc", "sort-desc")
          this.classList.add("sort-" + sortDirection)
        })
      })
    })
  }
}

/**
 * Trie un tableau HTML
 * @param {HTMLElement} table - Tableau à trier
 * @param {string} sortKey - Clé de tri
 * @param {string} direction - Direction du tri ('asc' ou 'desc')
 */
function sortTable(table, sortKey, direction) {
  const tbody = table.querySelector("tbody")
  const rows = Array.from(tbody.querySelectorAll("tr"))

  // Déterminer l'index de la colonne
  const headers = table.querySelectorAll("th")
  let columnIndex = 0

  for (let i = 0; i < headers.length; i++) {
    if (headers[i].getAttribute("data-sort") === sortKey) {
      columnIndex = i
      break
    }
  }

  // Trier les lignes
  rows.sort((a, b) => {
    const cellA = a.cells[columnIndex].textContent.trim()
    const cellB = b.cells[columnIndex].textContent.trim()

    // Vérifier si c'est un nombre
    if (!isNaN(cellA) && !isNaN(cellB)) {
      return direction === "asc"
        ? Number.parseFloat(cellA) - Number.parseFloat(cellB)
        : Number.parseFloat(cellB) - Number.parseFloat(cellA)
    }

    // Tri alphabétique
    return direction === "asc" ? cellA.localeCompare(cellB, "fr") : cellB.localeCompare(cellA, "fr")
  })

  // Réorganiser le tableau
  rows.forEach((row) => tbody.appendChild(row))
}

/**
 * Initialise la validation des formulaires
 */
function initFormValidation() {
  const forms = document.querySelectorAll(".admin-form")

  if (forms.length > 0) {
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        const requiredFields = form.querySelectorAll("[required]")
        let isValid = true

        requiredFields.forEach((field) => {
          // Réinitialiser l'état
          field.classList.remove("is-invalid")
          const errorMessage = field.parentNode.querySelector(".error-message")
          if (errorMessage) {
            errorMessage.remove()
          }

          // Vérifier si le champ est vide
          if (!field.value.trim()) {
            isValid = false
            field.classList.add("is-invalid")

            // Ajouter un message d'erreur
            const message = document.createElement("div")
            message.className = "error-message"
            message.textContent = "Ce champ est obligatoire"
            field.parentNode.appendChild(message)
          }

          // Validation spécifique pour les emails
          if (field.type === "email" && field.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (!emailRegex.test(field.value.trim())) {
              isValid = false
              field.classList.add("is-invalid")

              // Ajouter un message d'erreur
              const message = document.createElement("div")
              message.className = "error-message"
              message.textContent = "Adresse email invalide"
              field.parentNode.appendChild(message)
            }
          }

          // Validation spécifique pour les prix
          if (field.classList.contains("price-input") && field.value.trim()) {
            const priceValue = Number.parseFloat(field.value.trim())
            if (isNaN(priceValue) || priceValue <= 0) {
              isValid = false
              field.classList.add("is-invalid")

              // Ajouter un message d'erreur
              const message = document.createElement("div")
              message.className = "error-message"
              message.textContent = "Prix invalide"
              field.parentNode.appendChild(message)
            }
          }
        })

        if (!isValid) {
          e.preventDefault()

          // Faire défiler jusqu'au premier champ invalide
          const firstInvalid = form.querySelector(".is-invalid")
          if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" })
          }
        }
      })
    })
  }
}

/**
 * Initialise la prévisualisation des images
 */
function initImagePreview() {
  const imageInputs = document.querySelectorAll(".image-upload")

  if (imageInputs.length > 0) {
    imageInputs.forEach((input) => {
      const previewContainer = document.querySelector(input.getAttribute("data-preview"))

      if (previewContainer) {
        input.addEventListener("change", function () {
          // Vider le conteneur
          previewContainer.innerHTML = ""

          // Vérifier s'il y a des fichiers
          if (this.files && this.files.length > 0) {
            for (let i = 0; i < this.files.length; i++) {
              const file = this.files[i]

              // Vérifier si c'est une image
              if (file.type.startsWith("image/")) {
                const reader = new FileReader()

                reader.onload = (e) => {
                  const preview = document.createElement("div")
                  preview.className = "image-preview-item"

                  const img = document.createElement("img")
                  img.src = e.target.result
                  img.alt = "Aperçu"

                  preview.appendChild(img)
                  previewContainer.appendChild(preview)
                }

                reader.readAsDataURL(file)
              }
            }
          }
        })
      }
    })
  }
}

/**
 * Initialise les confirmations de suppression
 */
function initDeleteConfirmation() {
  const deleteButtons = document.querySelectorAll(".delete-btn")

  if (deleteButtons.length > 0) {
    deleteButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault()

        const confirmMessage = this.getAttribute("data-confirm") || "Êtes-vous sûr de vouloir supprimer cet élément ?"

        if (confirm(confirmMessage)) {
          // Si c'est un lien, suivre le lien
          if (this.tagName === "A") {
            window.location.href = this.href
          }
          // Si c'est un bouton dans un formulaire, soumettre le formulaire
          else if (this.form) {
            this.form.submit()
          }
        }
      })
    })
  }
}

/**
 * Initialise les graphiques
 */
function initCharts() {
  // Vérifier si Chart.js est disponible
  if (typeof Chart === "undefined") {
    return
  }

  // Graphique des ventes
  const salesChartCanvas = document.getElementById("salesChart")
  if (salesChartCanvas) {
    const ctx = salesChartCanvas.getContext("2d")

    // Récupérer les données du graphique
    const labels = JSON.parse(salesChartCanvas.getAttribute("data-labels") || "[]")
    const data = JSON.parse(salesChartCanvas.getAttribute("data-values") || "[]")

    new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Ventes (FCFA)",
            data: data,
            backgroundColor: "rgba(99, 102, 241, 0.2)",
            borderColor: "rgba(99, 102, 241, 1)",
            borderWidth: 2,
            tension: 0.3,
            pointBackgroundColor: "rgba(99, 102, 241, 1)",
            pointRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => value.toLocaleString("fr-FR") + " FCFA",
            },
          },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (context) => context.parsed.y.toLocaleString("fr-FR") + " FCFA",
            },
          },
        },
      },
    })
  }

  // Graphique des catégories
  const categoriesChartCanvas = document.getElementById("categoriesChart")
  if (categoriesChartCanvas) {
    const ctx = categoriesChartCanvas.getContext("2d")

    // Récupérer les données du graphique
    const labels = JSON.parse(categoriesChartCanvas.getAttribute("data-labels") || "[]")
    const data = JSON.parse(categoriesChartCanvas.getAttribute("data-values") || "[]")

    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: [
              "rgba(99, 102, 241, 0.7)",
              "rgba(16, 185, 129, 0.7)",
              "rgba(245, 158, 11, 0.7)",
              "rgba(239, 68, 68, 0.7)",
              "rgba(59, 130, 246, 0.7)",
              "rgba(168, 85, 247, 0.7)",
            ],
            borderColor: [
              "rgba(99, 102, 241, 1)",
              "rgba(16, 185, 129, 1)",
              "rgba(245, 158, 11, 1)",
              "rgba(239, 68, 68, 1)",
              "rgba(59, 130, 246, 1)",
              "rgba(168, 85, 247, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    })
  }
}

/**
 * Met à jour le statut d'une commande
 * @param {number} orderId - ID de la commande
 * @param {string} status - Nouveau statut
 * @param {HTMLElement} button - Bouton cliqué
 */
function updateOrderStatus(orderId, status, button) {
  // Désactiver le bouton pendant la requête
  button.disabled = true

  // Envoyer la requête AJAX
  fetch("api/orders.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=update_status&order_id=" + orderId + "&status=" + status,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Mettre à jour l'affichage
        const statusCell = button.closest("tr").querySelector(".order-status")
        if (statusCell) {
          // Supprimer les classes existantes
          statusCell.classList.remove("badge-success", "badge-warning", "badge-danger", "badge-info")

          // Ajouter la nouvelle classe
          switch (status) {
            case "pending":
              statusCell.classList.add("badge-warning")
              statusCell.textContent = "En attente"
              break
            case "processing":
              statusCell.classList.add("badge-info")
              statusCell.textContent = "En traitement"
              break
            case "completed":
              statusCell.classList.add("badge-success")
              statusCell.textContent = "Terminée"
              break
            case "cancelled":
              statusCell.classList.add("badge-danger")
              statusCell.textContent = "Annulée"
              break
          }
        }

        // Afficher un message de succès
        showToast("Statut mis à jour avec succès", "success")
      } else {
        showToast("Erreur: " + data.message, "error")
      }
    })
    .catch((error) => {
      console.error("Erreur:", error)
      showToast("Une erreur est survenue", "error")
    })
    .finally(() => {
      // Réactiver le bouton
      button.disabled = false
    })
}

/**
 * Affiche un toast (notification)
 * @param {string} message - Message à afficher
 * @param {string} type - Type de toast (success, error, warning, info)
 */
function showToast(message, type = "success") {
  const toast = document.createElement("div")
  toast.className = "admin-toast admin-toast-" + type
  toast.innerHTML = `
        <div class="admin-toast-content">
            <span class="admin-toast-message">${message}</span>
        </div>
        <button class="admin-toast-close">&times;</button>
    `

  document.body.appendChild(toast)

  // Afficher le toast
  setTimeout(() => {
    toast.classList.add("show")
  }, 10)

  // Fermer le toast au clic
  const closeBtn = toast.querySelector(".admin-toast-close")
  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      toast.classList.remove("show")
      setTimeout(() => toast.remove(), 300)
    })
  }

  // Auto-fermeture après 3 secondes
  setTimeout(() => {
    toast.classList.remove("show")
    setTimeout(() => toast.remove(), 300)
  }, 3000)
}
