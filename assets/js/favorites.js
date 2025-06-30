/**
 * Gestion des favoris - JavaScript
 * ElvyMade - Site de prospection d'articles
 */

// Fonction pour basculer un favori
// Fonction pour basculer un favori
function toggleFavorite(productId, buttonElement = null) {
  // Trouver le bouton concerné
  const favoriteBtn =
    buttonElement ||
    document.querySelector(
      `.favorite-btn[data-product-id="${productId}"], .favorite-btn-large[data-product-id="${productId}"]`,
    )

  if (!favoriteBtn) {
    console.error("Bouton favori non trouvé pour le produit", productId)
    return
  }

  const isFavorite = favoriteBtn.classList.contains("active")
  const method = isFavorite ? "DELETE" : "POST"

  // URL de l'API - toujours absolu
  const baseUrl = "/api/favorites.php"
  const url = isFavorite ? `${baseUrl}?product_id=${productId}` : baseUrl

  // Désactiver le bouton pendant la requête
  favoriteBtn.disabled = true
  favoriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'

  const requestOptions = {
    method: method,
    headers: {
      "Content-Type": "application/json",
    },
  }

  if (!isFavorite) {
    requestOptions.body = JSON.stringify({
      product_id: productId,
    })
  }

  fetch(url, requestOptions)
    .then((response) => {
      if (!response.ok) {
        return response.json().then((err) => Promise.reject(err))
      }
      return response.json()
    })
    .then((data) => {
      // Afficher la réponse complète de l'API pour le debug
      showNotification(JSON.stringify(data), data.success ? "success" : "error")
      if (data.success) {
        // Mettre à jour l'état visuel du bouton
        favoriteBtn.classList.toggle("active")
        favoriteBtn.title = favoriteBtn.classList.contains("active") ? "Retirer des favoris" : "Ajouter aux favoris"
        favoriteBtn.innerHTML = '<i class="fas fa-heart"></i>'

        // Afficher une notification
        const message = isFavorite ? "Produit retiré des favoris" : "Produit ajouté aux favoris"
        showNotification(message, "success")
        // Mettre à jour le compteur de favoris
        updateFavoritesCount(isFavorite ? -1 : 1)
        // Suppression instantanée du bloc sur la page favoris
        if (isFavorite && window.location.pathname.includes('favorites.php')) {
          removeFavoriteFromDOM(productId)
        }
      } else {
        throw new Error(data.message || "Erreur inconnue")
      }
    })
    .catch((error) => {
      console.error("Erreur lors de la gestion du favori:", error)

      // Réinitialiser le bouton en cas d'erreur
      favoriteBtn.innerHTML = '<i class="fas fa-heart"></i>'

      if (error.message && error.message.includes("connecté")) {
        showNotification("Vous devez être connecté pour gérer vos favoris", "error")
        // Rediriger vers la page de connexion après un délai
        setTimeout(() => {
          const loginUrl = isInPagesFolder ? "login.php" : "pages/login.php"
          window.location.href = loginUrl + "?redirect=" + encodeURIComponent(window.location.pathname)
        }, 2000)
      } else {
        showNotification(error.message || "Erreur lors de la gestion du favori", "error")
      }
    })
    .finally(() => {
      // Réactiver le bouton
      favoriteBtn.disabled = false
    })
}

// Nouvelle fonction pour mettre à jour l'état du bouton
function updateFavoriteButtonState(button, isActive) {
  if (isActive) {
    button.classList.add("active")
    button.title = "Retirer des favoris"

    // Mettre à jour le texte si c'est un bouton large
    const btnText = button.querySelector("span")
    if (btnText) {
      btnText.textContent = "Retirer des favoris"
    }
  } else {
    button.classList.remove("active")
    button.title = "Ajouter aux favoris"

    // Mettre à jour le texte si c'est un bouton large
    const btnText = button.querySelector("span")
    if (btnText) {
      btnText.textContent = "Ajouter aux favoris"
    }
  }
}

// Fonction pour supprimer un favori du DOM (page favoris)
function removeFavoriteFromDOM(productId) {
  const productCard = document.querySelector(`[data-product-id="${productId}"]`)
  if (productCard) {
    productCard.style.animation = "fadeOut 0.3s ease-out"
    setTimeout(() => {
      productCard.remove()
      // Vérifier s'il reste des favoris
      const remainingCards = document.querySelectorAll(".favorite-item")
      if (remainingCards.length === 0) {
        // Afficher le message "aucun favori" sans recharger
        const grid = document.querySelector('.favorites-grid')
        if (grid) grid.innerHTML = `<div style='text-align:center;padding:var(--spacing-12) 0;background:var(--gray-50);border-radius:var(--border-radius-xl);margin:var(--spacing-6) 0;'><div style='font-size:var(--font-size-5xl);color:var(--primary-light);margin-bottom:var(--spacing-4);'><i class='fas fa-heart-broken'></i></div><h3 style='font-size:var(--font-size-2xl);color:var(--gray-800);margin-bottom:var(--spacing-2);'>Aucun favori pour le moment</h3><p style='color:var(--gray-600);margin-bottom:var(--spacing-6);max-width:500px;margin-left:auto;margin-right:auto;'>Explorez nos produits et ajoutez vos coups de cœur à vos favoris pour les retrouver facilement.</p><div style='display:flex;gap:var(--spacing-4);justify-content:center;'><a href='../index.php' class='btn btn-primary'><i class='fas fa-home'></i> Découvrir les produits</a><a href='search.php' class='btn btn-outline'><i class='fas fa-search'></i> Rechercher</a></div></div>`
      }
    }, 300)
  }
  updateFavoritesCount(-1)
}

function updateFavoritesCount(change) {
  const countElements = document.querySelectorAll(".favorite-count, .mobile-favorite-count")
  countElements.forEach((countElement) => {
    if (countElement) {
      let currentCount = Number.parseInt(countElement.textContent) || 0
      currentCount += change
      currentCount = Math.max(0, currentCount) // S'assurer que le compte ne devient pas négatif
      countElement.textContent = currentCount
      countElement.style.display = 'inline' // Toujours visible
    }
  })
}

// Fonction pour afficher une notification
function showNotification(message, type = "info") {
  // Supprimer les notifications existantes
  const existingNotifications = document.querySelectorAll(".notification")
  existingNotifications.forEach((notif) => notif.remove())

  // Créer la nouvelle notification
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `

  // Ajouter au DOM
  document.body.appendChild(notification)

  // Animation d'entrée
  setTimeout(() => {
    notification.classList.add("show")
  }, 100)

  // Suppression automatique après 5 secondes
  setTimeout(() => {
    if (notification.parentElement) {
      notification.classList.remove("show")
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove()
        }
      }, 300)
    }
  }, 5000)
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  // Ajouter les styles CSS pour les notifications si ils n'existent pas
  if (!document.querySelector("#notification-styles")) {
    const style = document.createElement("style")
    style.id = "notification-styles"
    style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                padding: 16px;
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 10000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 400px;
                border-left: 4px solid #6b7280;
            }
            
            .notification.show {
                transform: translateX(0);
            }
            
            .notification-success {
                border-left-color: #10b981;
            }
            
            .notification-error {
                border-left-color: #ef4444;
            }
            
            .notification-info {
                border-left-color: #3b82f6;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 8px;
                flex: 1;
            }
            
            .notification-success .notification-content i {
                color: #10b981;
            }
            
            .notification-error .notification-content i {
                color: #ef4444;
            }
            
            .notification-info .notification-content i {
                color: #3b82f6;
            }
            
            .notification-close {
                background: none;
                border: none;
                color: #6b7280;
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                transition: background-color 0.2s;
            }
            
            .notification-close:hover {
                background-color: #f3f4f6;
            }
            
            @media (max-width: 768px) {
                .notification {
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }
            }
        `
    document.head.appendChild(style)
  }
})
