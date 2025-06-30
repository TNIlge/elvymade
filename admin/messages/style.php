/* Styles généraux pour la messagerie admin */
.admin-messages {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    line-height: 1.6;
}

/* Cartes de statistiques */
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.admin-stat-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 1.5rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1.5rem;
}

.stat-icon.bg-blue {
    background-color: #3b82f6;
}

.stat-icon.bg-orange {
    background-color: #f97316;
}

.stat-icon.bg-green {
    background-color: #22c55e;
}

.stat-content h3 {
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.stat-content p {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

/* Tableau des messages */
.admin-table-responsive {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    text-align: left;
    padding: 0.75rem 1rem;
    background-color: #f1f5f9;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.admin-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.admin-table tr.unread td {
    background-color: rgba(59, 130, 246, 0.05);
}

/* Badges */
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-unread {
    background-color: #fee2e2;
    color: #b91c1c;
}

.badge-read {
    background-color: #fef3c7;
    color: #92400e;
}

.badge-replied {
    background-color: #dcfce7;
    color: #166534;
}

/* Actions du tableau */
.table-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f1f5f9;
    color: #64748b;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action:hover {
    background-color: #e2e8f0;
    color: #334155;
}

.btn-action i {
    font-size: 0.875rem;
}

.btn-delete:hover {
    background-color: #fee2e2;
    color: #b91c1c;
}

/* Pagination */
.admin-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 2rem;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.5rem 1rem;
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    transition: all 0.2s;
}

.pagination-btn:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1;
}

.pagination-numbers {
    display: flex;
    gap: 0.25rem;
}

.pagination-numbers a {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    color: #334155;
    font-weight: 500;
    transition: all 0.2s;
}

.pagination-numbers a:hover,
.pagination-numbers a.active {
    background-color: #3b82f6;
    color: white;
}

/* Cartes de message */
.admin-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-body {
    padding: 1.5rem;
}

/* Détails du message */
.message-details {
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.detail-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-row strong {
    min-width: 120px;
    color: #334155;
    font-weight: 500;
}

.detail-row span {
    color: #64748b;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.avatar, .avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #94a3b8;
}

.message-content h3 {
    font-size: 1rem;
    color: #1e293b;
    margin-bottom: 1rem;
}

.message-text {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 0.375rem;
    border-left: 4px solid #3b82f6;
    line-height: 1.6;
    color: #334155;
}

.response-text {
    background: rgba(34, 197, 94, 0.05);
    padding: 1.5rem;
    border-radius: 0.375rem;
    border-left: 4px solid #22c55e;
    line-height: 1.6;
    color: #334155;
}

/* Formulaire */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #334155;
}

.form-label .required {
    color: #ef4444;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.5);
}

.form-textarea {
    width: 100%;
    min-height: 200px;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    font-size: 1rem;
    line-height: 1.6;
    resize: vertical;
    transition: all 0.2s;
}

.form-textarea:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.5);
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* Boutons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.25rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
    gap: 0.5rem;
    cursor: pointer;
    border: none;
}

.btn-admin-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-admin-primary:hover {
    background-color: #2563eb;
}

.btn-admin-secondary {
    background-color: white;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.btn-admin-secondary:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1;
}

/* Grille admin */
.admin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }
}

/* État vide */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
}

.empty-state i {
    font-size: 3rem;
    color: #94a3b8;
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Alertes */
.alert {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-error {
    background-color: #fee2e2;
    color: #b91c1c;
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
}

.alert i {
    font-size: 1.25rem;
}

.alert span {
    font-weight: 500;
}