/**
 * Script d'intégration WhatsApp pour ElvyMade
 * Fonctionnalités de communication via WhatsApp
 */

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les boutons WhatsApp
  initWhatsAppButtons()
})

/**
 * Initialise les boutons WhatsApp
 */
function initWhatsAppButtons() {
  // Bouton WhatsApp flottant
  const whatsappFloat = document.querySelector(".whatsapp-float")
  if (whatsappFloat) {
    whatsappFloat.addEventListener("click", (e) => {
      e.preventDefault()
      openWhatsAppGeneral()
    })
  }

  // Boutons WhatsApp des produits
  const productWhatsAppButtons = document.querySelectorAll(".product-whatsapp-btn")
  if (productWhatsAppButtons.length > 0) {
    productWhatsAppButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault()
        const productId = this.getAttribute("data-product-id")
        const productName = this.getAttribute("data-product-name")
        openWhatsAppProduct(productName, productId)
      })
    })
  }
}

/**
 * Ouvre WhatsApp avec un message général
 */
function openWhatsAppGeneral() {
  // Numéro WhatsApp (sans le +)
  const number = "+237658470529" // À remplacer par le numéro configuré

  // Message général
  const message = "Bonjour ! Je suis intéressé par vos produits sur ElvyMade."

  // Construire l'URL WhatsApp
  const url = `https://wa.me/${number}?text=${encodeURIComponent(message)}`

  // Ouvrir dans un nouvel onglet
  window.open(url, "_blank")
}

/**
 * Ouvre WhatsApp avec un message concernant un produit spécifique
 * @param {string} productName - Nom du produit
 * @param {string|number} productId - ID du produit
 */
function openWhatsAppProduct(productName, productId) {
  // Numéro WhatsApp (sans le +)
  const number = "+237658470529" // À remplacer par le numéro configuré

  // Message avec référence au produit
  const message = `Bonjour ! Je suis intéressé par ce produit : ${productName} (Réf: ${productId})`

  // Construire l'URL WhatsApp
  const url = `https://wa.me/${number}?text=${encodeURIComponent(message)}`

  // Ouvrir dans un nouvel onglet
  window.open(url, "_blank")
}

/**
 * Ouvre WhatsApp avec un message concernant une catégorie
 * @param {string} category - Nom de la catégorie
 */
function openWhatsAppCategory(category) {
  // Numéro WhatsApp (sans le +)
  const number = "+237658470529" // À remplacer par le numéro configuré

  // Message avec référence à la catégorie
  const message = `Bonjour ! Je cherche des produits dans la catégorie : ${category}`

  // Construire l'URL WhatsApp
  const url = `https://wa.me/${number}?text=${encodeURIComponent(message)}`

  // Ouvrir dans un nouvel onglet
  window.open(url, "_blank")
}

/**
 * Ouvre WhatsApp avec un message de commande
 * @param {string|number} orderId - ID de la commande
 * @param {number} total - Montant total de la commande
 */
function openWhatsAppOrder(orderId, total) {
  // Numéro WhatsApp (sans le +)
  const number = "+237658470529" // À remplacer par le numéro configuré

  // Formater le total
  const formattedTotal = new Intl.NumberFormat("fr-FR").format(total) + " FCFA"

  // Message avec référence à la commande
  const message = `Bonjour ! Je souhaite confirmer ma commande n°${orderId} d'un montant de ${formattedTotal}.`

  // Construire l'URL WhatsApp
  const url = `https://wa.me/${number}?text=${encodeURIComponent(message)}`

  // Ouvrir dans un nouvel onglet
  window.open(url, "_blank")
}

/**
 * Ouvre WhatsApp avec un message personnalisé
 * @param {string} customMessage - Message personnalisé
 */
function openWhatsAppCustom(customMessage) {
  // Numéro WhatsApp (sans le +)
  const number = "+237658470529" // À remplacer par le numéro configuré

  // Construire l'URL WhatsApp
  const url = `https://wa.me/${number}?text=${encodeURIComponent(customMessage)}`

  // Ouvrir dans un nouvel onglet
  window.open(url, "_blank")
}

/**
 * Partage un produit via WhatsApp
 * @param {string|number} productId - ID du produit
 * @param {string} productName - Nom du produit
 */
function shareProductViaWhatsApp(productId, productName) {
  // URL du produit
  const productUrl = `${window.location.origin}/pages/product.php?id=${productId}`

  // Message de partage
  const message = `Découvre ce produit sur ElvyMade : ${productName} - ${productUrl}`

  // Construire l'URL WhatsApp
  const url = `https://wa.me/?text=${encodeURIComponent(message)}`

  // Ouvrir dans un nouvel onglet
  window.open(url, "_blank")
}
