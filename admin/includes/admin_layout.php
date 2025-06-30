<?php
/**
 * Layout principal de l'administration
 * ElvyMade - Site de prospection d'articles
 */
?>

<style>
/* Styles pour le layout principal */
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f5f5;
}

.admin-layout {
    display: flex;
    min-height: 100vh;
}

.admin-main {
    flex: 1;
    margin-left: 250px;
    display: flex;
    flex-direction: column;
    transition: margin-left 0.3s ease;
}

.admin-content {
    flex: 1;
    padding: 20px;
    background-color: #f5f5f5;
}

/* Styles responsifs */
@media (max-width: 768px) {
    .admin-main {
        margin-left: 0;
    }
}
</style>
