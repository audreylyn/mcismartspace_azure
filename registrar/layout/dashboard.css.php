<style>
    :root {
    --primary-color: #1e5631;
    /* Dark forest green */
    --secondary-color: #d4af37;
    /* Timeless gold */
    --accent-color: #0f2d1a;
    /* Darker green */
    --success-color: #2e7d32;
    /* Forest green */
    --danger-color: #c62828;
    /* Deep red */
    --warning-color: #f9a825;
    /* Gold-yellow */
    --info-color: #0277bd;
    /* Deep blue */
    --text-color: #333;
    --text-muted: #6c757d;
    --bg-color: #f8f9fa;
    --card-bg: #ffffff;
    --border-radius: 12px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s ease;
}

body {
    background-color: var(--bg-color);
    color: var(--text-color);
}

.dashboard-container {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 20px;
    padding: 20px;
}

.stat-card {
    grid-column: span 3;
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 24px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border-left: 4px solid var(--primary-color);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.chart-card {
    grid-column: span 6;
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    transition: var(--transition);
}

.chart-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.chart-card-full {
    grid-column: span 12;
}

.issues-card {
    grid-column: span 12;
}

.stat-value {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.stat-label {
    font-size: 1rem;
    color: var(--text-muted);
    font-weight: 500;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding-bottom: 15px;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.card-title .icon {
    margin-right: 8px;
    color: var(--primary-color);
}

.card-content {
    position: relative;
}

.issue-item {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    background-color: rgba(0, 0, 0, 0.02);
    transition: var(--transition);
}

.issue-item:hover {
    background-color: rgba(0, 0, 0, 0.04);
}

.issue-item:last-child {
    margin-bottom: 0;
}

.issue-title {
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 1.05rem;
}

.issue-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-pending {
    background-color: var(--warning-color);
    color: #000;
}

.badge-in-progress {
    background-color: var(--info-color);
    color: #fff;
}

.badge-resolved {
    background-color: var(--success-color);
    color: #fff;
}

.badge-rejected {
    background-color: var(--danger-color);
    color: #fff;
}

.action-link {
    display: inline-block;
    padding: 8px 16px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    text-align: center;
    margin-top: 16px;
}

.action-link:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
}

.stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 2.5rem;
    color: var(--secondary-color);
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .stat-card {
        grid-column: span 6;
    }

    .chart-card {
        grid-column: span 12;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
        padding: 15px;
    }

    .stat-card {
        grid-column: span 3;
    }

    .chart-card,
    .issues-card {
        grid-column: span 6;
    }
}

@media (max-width: 576px) {
    .stat-card {
        grid-column: span 6;
    }
}

.quick-links {
    grid-column: span 12;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.quick-link-card {
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    display: flex;
    flex-direction: column;
    transition: var(--transition);
    height: 100%;
}

.quick-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.quick-link-header {
    margin-bottom: 16px;
}

.quick-link-title {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 4px;
}

.quick-link-subtitle {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.quick-link-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 16px;
    opacity: 0.8;
}

.quick-link-footer {
    margin-top: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.quick-link-btn {
    flex: 1;
    padding: 8px 12px;
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: var(--transition);
}

.quick-link-btn:hover {
    background-color: var(--primary-color);
    color: white;
}

/* Main section adjustments */
.section.main-section {
    padding: 0;
    margin: 0;
    max-width: 100%;
}

/* Style updates to match site theme */
.stat-card .stat-icon {
    color: var(--primary-color);
}

.stat-value {
    color: var(--primary-color);
}

.chart-header h3 {
    color: var(--primary-color);
}

.status-badge.pending {
    background-color: var(--warning-color);
}

.status-badge.in-progress {
    background-color: var(--info-color);
}

.status-badge.resolved {
    background-color: var(--success-color);
}

.status-badge.rejected {
    background-color: var(--danger-color);
}

.issues-header h3 {
    color: var(--primary-color);
}

/* Dropdown fix */
.navbar-item.dropdown.is-active .navbar-dropdown {
    display: block;
}

.navbar-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    z-index: 20;
    background-color: white;
    border-radius: 4px;
    box-shadow: 0 0.5em 1em -0.125em rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.02);
}

.table-card {
    grid-column: span 12;
    background-color: var(--card-bg);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
    transition: var(--transition);
    margin-bottom: 20px;
}

.table-responsive {
    overflow-x: auto;
}

.dashboard-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.dashboard-table th,
.dashboard-table td {
    padding: 12px 15px;
    text-align: left;
}

.dashboard-table thead tr {
    background-color: rgba(0, 0, 0, 0.02);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.dashboard-table tbody tr {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.dashboard-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>
