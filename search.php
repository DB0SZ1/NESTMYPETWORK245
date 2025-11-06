<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require 'db.php';

// Get filter parameters with proper sanitization
$serviceType = isset($_GET['service_type']) ? trim($_GET['service_type']) : '';
$location = isset($_GET['address']) ? trim($_GET['address']) : '';
$verifiedOnly = isset($_GET['star_sitter']) ? (bool)$_GET['star_sitter'] : false;
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 10000;

// Fetch sitters with proper filtering
$sitters = [];
try {
    // Build comprehensive query with proper JOINs
    $sql = "
        SELECT DISTINCT
            u.id,
            u.fullname,
            u.city,
            u.country,
            u.postcode,
            u.profile_photo_path,
            u.sitter_status,
            hp.home_type,
            hp.outdoor_space,
            hp.years_experience,
            hp.sitter_role
        FROM users u
        LEFT JOIN host_profiles hp ON u.id = hp.user_id
        WHERE u.is_sitter = 1
    ";

    $params = [];
    $conditions = [];

    // Apply verified filter
    if ($verifiedOnly) {
        $conditions[] = "u.sitter_status = 'approved'";
    }

    // Apply location filter (comprehensive search across all location fields)
    if (!empty($location)) {
        $conditions[] = "(
            u.city LIKE :location OR 
            u.country LIKE :location OR 
            u.postcode LIKE :location OR 
            u.street LIKE :location OR
            u.address_city LIKE :location OR 
            u.address_country LIKE :location OR 
            u.address_postcode LIKE :location
        )";
        $params[':location'] = '%' . $location . '%';
    }

    // Apply service type filter - check both sitter_services and host_services tables
    if (!empty($serviceType)) {
        $conditions[] = "(
            EXISTS (
                SELECT 1 FROM sitter_services ss 
                WHERE ss.user_id = u.id 
                AND ss.service_type = :service_type1
            )
            OR EXISTS (
                SELECT 1 FROM host_services hs 
                WHERE hs.host_user_id = u.id 
                AND hs.service_name = :service_type2
            )
        )";
        $params[':service_type1'] = $serviceType;
        $params[':service_type2'] = $serviceType;
    }

    // Apply price filter - check sitter_services table
    if ($minPrice > 0 || $maxPrice < 10000) {
        $conditions[] = "EXISTS (
            SELECT 1 FROM sitter_services ss 
            WHERE ss.user_id = u.id 
            AND ss.price_per_night BETWEEN :min_price AND :max_price
        )";
        $params[':min_price'] = $minPrice;
        $params[':max_price'] = $maxPrice;
    }

    // Add all conditions to query
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // Add ordering
    $sql .= " ORDER BY 
        CASE WHEN u.sitter_status = 'approved' THEN 0 ELSE 1 END,
        u.id DESC
    ";

    // Execute main query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $userResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If we have results, fetch their services and additional data
    if (!empty($userResults)) {
        $userIds = array_column($userResults, 'id');
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        // Fetch all services from sitter_services table
        $servicesSql = "
            SELECT 
                user_id,
                service_type,
                price_per_night,
                headline,
                sitter_about_me
            FROM sitter_services
            WHERE user_id IN ($placeholders)
        ";
        $servicesStmt = $pdo->prepare($servicesSql);
        $servicesStmt->execute($userIds);
        $servicesData = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all services from host_services table
        $hostServicesSql = "
            SELECT 
                host_user_id as user_id,
                service_name as service_type,
                max_pets,
                breed_size_restrictions,
                can_administer_meds,
                has_emergency_transport
            FROM host_services
            WHERE host_user_id IN ($placeholders)
        ";
        $hostServicesStmt = $pdo->prepare($hostServicesSql);
        $hostServicesStmt->execute($userIds);
        $hostServicesData = $hostServicesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize services by user_id
        $servicesMap = [];
        
        // Add sitter_services with pricing
        foreach ($servicesData as $service) {
            $userId = $service['user_id'];
            if (!isset($servicesMap[$userId])) {
                $servicesMap[$userId] = [];
            }
            $servicesMap[$userId][] = $service;
        }

        // Merge host_services (these might not have pricing in sitter_services)
        foreach ($hostServicesData as $hostService) {
            $userId = $hostService['user_id'];
            $serviceType = $hostService['service_type'];
            
            // Check if this service already exists in servicesMap with pricing
            $exists = false;
            if (isset($servicesMap[$userId])) {
                foreach ($servicesMap[$userId] as $existingService) {
                    if ($existingService['service_type'] === $serviceType) {
                        $exists = true;
                        break;
                    }
                }
            }
            
            // If not exists, add it (but it won't have pricing)
            if (!$exists) {
                if (!isset($servicesMap[$userId])) {
                    $servicesMap[$userId] = [];
                }
                $servicesMap[$userId][] = [
                    'service_type' => $serviceType,
                    'price_per_night' => 0, // Default price if not set
                    'headline' => '',
                    'sitter_about_me' => ''
                ];
            }
        }

        // Combine all data into final sitters array
        foreach ($userResults as &$user) {
            $userId = $user['id'];
            
            // Add services array
            $user['services'] = isset($servicesMap[$userId]) ? $servicesMap[$userId] : [];
            
            // Add headline from first service or default
            if (!empty($user['services'])) {
                $user['headline'] = $user['services'][0]['headline'] ?? '';
                $user['about_me'] = $user['services'][0]['sitter_about_me'] ?? '';
            } else {
                $user['headline'] = '';
                $user['about_me'] = '';
            }
        }

        $sitters = $userResults;
    }

} catch (PDOException $e) {
    error_log("Search Error: " . $e->getMessage());
    error_log("Query: " . $sql);
    error_log("Params: " . print_r($params, true));
    $sitters = [];
}

// Count statistics
$totalSitters = count($sitters);
$verifiedCount = count(array_filter($sitters, fn($s) => $s['sitter_status'] === 'approved'));
$unverifiedCount = $totalSitters - $verifiedCount;

// Count total available services across all sitters
$totalServices = 0;
foreach ($sitters as $sitter) {
    if (!empty($sitter['services'])) {
        $totalServices += count($sitter['services']);
    }
}

// Get unique service names for display
$allServiceNames = [];
foreach ($sitters as $sitter) {
    if (!empty($sitter['services'])) {
        foreach ($sitter['services'] as $service) {
            $serviceName = ucfirst($service['service_type']);
            if (!in_array($serviceName, $allServiceNames)) {
                $allServiceNames[] = $serviceName;
            }
        }
    }
}

$pageTitle = "Find a Sitter";
include 'header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
/* ===================================
   REVAMPED SEARCH - MODERN DESIGN
   =================================== */

:root {
    --search-primary: #00a862;
    --search-secondary: #667eea;
    --search-accent: #FF6B35;
    --search-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #00a862, #00c875);
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
}

body {
    background: #f8f9fa;
}

.search-page-wrapper {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

/* ===================================
   SEARCH HERO
   =================================== */
.search-hero {
    background: white;
    padding: 3rem 0 2rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.search-hero-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
    text-align: center;
}

.search-hero h1 {
    color: #1a1a1a;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.search-hero h1 i {
    color: var(--search-primary);
    font-size: 2.25rem;
}

.search-hero-subtitle {
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 2.5rem;
}



/* ===================================
   SEARCH LAYOUT
   =================================== */
.search-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 2rem;
    align-items: start;
}

/* ===================================
   FILTERS SIDEBAR
   =================================== */
.filters-sidebar {
    position: sticky;
    top: 100px;
}

.filters-card {
    background: white;
    border-radius: 24px;
    padding: 2rem;
    box-shadow: var(--shadow-md);
    position: relative;
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.filters-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--success-gradient);
}
.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.filters-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filters-title i {
    color: var(--search-primary);
}

.reset-filters {
    color: var(--search-accent);
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.reset-filters:hover {
    color: #e85a2a;
}

.filter-group {
    margin-bottom: 1.5rem;
}

.filter-label {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    font-family: inherit;
    background: white;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--search-primary);
    box-shadow: 0 0 0 4px rgba(0, 168, 98, 0.1);
    transform: translateY(-1px);
}

.filter-input:hover,
.filter-select:hover {
    border-color: #c0c0c0;
    transition: all 0.2s ease;
}
.filter-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

.price-range-inputs {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 0.5rem;
    align-items: center;
}

.price-separator {
    color: #999;
    font-weight: 600;
}

.verified-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    transition: all 0.2s ease;
}

.verified-toggle:hover {
    border-color: var(--search-primary);
}

.verified-toggle-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1a1a1a;
}

.verified-toggle-label i {
    color: var(--search-primary);
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 26px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background: var(--success-gradient);
}

input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.apply-filters-btn {
    width: 100%;
    padding: 1rem;
    background: var(--success-gradient);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 168, 98, 0.3);
}

.apply-filters-btn:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 24px rgba(0, 168, 98, 0.4);
}

.apply-filters-btn:active {
    transform: translateY(0) scale(0.98);
}

.active-filters {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #f0f0f0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    background: #e3f2fd;
    color: #1976d2;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

/* ===================================
   RESULTS SECTION
   =================================== */
.results-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.results-header {
    background: white;
    border-radius: 16px;
    padding: 1.5rem 2rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.results-header::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--success-gradient);
}

.results-info h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.results-count {
    color: #666;
    font-size: 1rem;
}

.results-count strong {
    color: var(--search-primary);
    font-weight: 700;
}

.sort-dropdown {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sort-dropdown label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #666;
}

.sort-dropdown select {
    padding: 0.75rem 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 0.9rem;
    cursor: pointer;
    min-width: 150px;
}

/* ===================================
   SITTER CARDS
   =================================== */
.sitters-grid {
    display: grid;
    gap: 1.5rem;
}

.sitter-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1.5rem;
    align-items: start;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.sitter-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 168, 98, 0.05), rgba(102, 126, 234, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.sitter-card-modern:hover {
    transform: translateY(-6px) scale(1.01);
    box-shadow: 0 12px 40px rgba(0, 168, 98, 0.15);
    border-color: var(--search-primary);
}

.sitter-card-modern:hover::before {
    opacity: 1;
}

.sitter-card-modern.verified {
    border: 2px solid #e9ecef;
}

.sitter-card-modern.verified:hover {
    border-color: var(--search-primary);
}

.sitter-avatar-section {
    position: relative;
}

.sitter-avatar-large {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #f0f0f0;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.sitter-card-modern:hover .sitter-avatar-large {
    transform: scale(1.05);
    border-color: var(--search-primary);
}

.sitter-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-large {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e0e0e0, #f5f5f5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-placeholder-large i {
    font-size: 48px;
    color: #bbb;
}

.verified-badge-overlay {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 36px;
    height: 36px;
    background: var(--search-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid white;
    box-shadow: var(--shadow-sm);
}

.verified-badge-overlay i {
    color: white;
    font-size: 16px;
}

@keyframes pulse-verified {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(0, 168, 98, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(0, 168, 98, 0);
    }
}

.verified-badge-overlay {
    animation: pulse-verified 2s ease-in-out infinite;
}

.sitter-info-section {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.sitter-name-row {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sitter-name-modern {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.2;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.verified {
    background: #d4edda;
    color: #155724;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.sitter-location-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.95rem;
}

.sitter-location-modern i {
    color: var(--search-accent);
}

.sitter-headline-modern {
    font-size: 0.95rem;
    color: #555;
    line-height: 1.5;
    margin: 0.4rem 0;
}
.sitter-services {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.service-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #e3f2fd, #f1f8ff);
    color: #1976d2;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: default;
}

.sitter-card-modern:hover .service-tag {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(25, 118, 210, 0.15);
}

.service-tag i {
    font-size: 0.9rem;
}

.service-price-tag {
    color: var(--search-accent);
    margin-left: 0.25rem;
}

.sitter-features {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.4rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.feature-item i {
    color: var(--search-primary);
}

.sitter-pricing-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1rem;
    text-align: right;
}

.price-display-modern {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.price-from {
    font-size: 0.85rem;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-amount-modern {
    font-size: 2rem;
    font-weight: 700;
    color: var(--search-primary);
    line-height: 1;
}

.price-period {
    font-size: 0.9rem;
    color: #666;
}

.view-profile-btn {
    padding: 0.875rem 2rem;
    background: var(--success-gradient);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 168, 98, 0.2);
}

.view-profile-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 8px 24px rgba(0, 168, 98, 0.5);
}

.view-profile-btn:active {
    transform: translateY(0) scale(0.95);
}

/* ===================================
   EMPTY STATE
   =================================== */
.empty-state-modern {
    background: white;
    border-radius: 20px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: var(--shadow-sm);
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
}

.empty-icon i {
    font-size: 56px;
    color: #ccc;
}

.empty-state-modern h3 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 1rem;
}

.empty-state-modern p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
}

.reset-search-btn {
    padding: 1rem 2rem;
    background: var(--success-gradient);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.reset-search-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* ===================================
   LOADING STATE
   =================================== */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-overlay.active {
    display: flex;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--search-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===================================
   ANIMATIONS
   =================================== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sitter-card-modern {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}

.sitter-card-modern:nth-child(1) { animation-delay: 0.05s; }
.sitter-card-modern:nth-child(2) { animation-delay: 0.1s; }
.sitter-card-modern:nth-child(3) { animation-delay: 0.15s; }
.sitter-card-modern:nth-child(4) { animation-delay: 0.2s; }
.sitter-card-modern:nth-child(5) { animation-delay: 0.25s; }
.sitter-card-modern:nth-child(6) { animation-delay: 0.3s; }
.sitter-card-modern:nth-child(7) { animation-delay: 0.35s; }
.sitter-card-modern:nth-child(8) { animation-delay: 0.4s; }
/* ===================================
   RESPONSIVE DESIGN
   =================================== */
@media (max-width: 1200px) {
    .search-container {
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 992px) {
    .search-container {
        grid-template-columns: 1fr;
    }
    
    .filters-sidebar {
        position: relative;
        top: 0;
    }
    
    .sitter-card-modern {
        grid-template-columns: auto 1fr;
        gap: 1.5rem;
    }
    
    .sitter-pricing-section {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

@media (max-width: 768px) {
    .search-hero h1 {
        font-size: 2rem;
    }
    
    .search-stats-bar {
        gap: 1.5rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .search-container {
        padding: 0 1rem;
    }
    
    .results-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .sitter-card-modern {
        grid-template-columns: 1fr;
        text-align: center;
        padding: 1.5rem;
    }
    
    .sitter-avatar-section {
        margin: 0 auto;
    }
    
    .sitter-info-section {
        align-items: center;
    }
    
    .sitter-name-row {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .sitter-features {
        justify-content: center;
    }
    
    .sitter-pricing-section {
        align-items: center;
        text-align: center;
    }
    
    .price-range-inputs {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .price-separator {
        display: none;
    }
}

@media (max-width: 480px) {
    .search-hero {
        padding: 2rem 0;
    }
    
    .search-hero h1 {
        font-size: 1.75rem;
    }
    
    .filters-card {
        padding: 1.5rem;
    }
    
      .sitter-avatar-large {
        width: 90px;
        height: 90px;
    }

    
    .sitter-name-modern {
        font-size: 1.25rem;
    }
    
    .price-amount-modern {
        font-size: 1.75rem;
    }
}
</style>

<main class="search-page-wrapper">
    <!-- Hero Section -->
    <section class="search-hero">
        <div class="search-hero-content">
            <h1><i class="fa-solid fa-magnifying-glass"></i> Find Your Perfect Pet Sitter</h1>
<p class="search-hero-subtitle">Browse available pet sitters and find the perfect match for your furry friend</p>
            
            
        </div>
    </section>

    <!-- Search Container -->
    <div class="search-container">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <div class="filters-card">
                <div class="filters-header">
                    <h3 class="filters-title">
                        <i class="fa-solid fa-sliders"></i>
                        Filters
                    </h3>
                    <a href="search.php" class="reset-filters">
                        <i class="fa-solid fa-rotate-right"></i> Reset
                    </a>
                </div>

                <form action="search.php" method="GET" id="searchForm">
                    <!-- Service Type Filter -->
                    <div class="filter-group">
                        <label for="service_type" class="filter-label">
                            <i class="fa-solid fa-briefcase"></i> Service Type
                        </label>
                        <select id="service_type" name="service_type" class="filter-select">
                            <option value="">All Services</option>
                            <option value="boarding" <?php echo $serviceType === 'boarding' ? 'selected' : ''; ?>>Boarding</option>
                            <option value="housesitting" <?php echo $serviceType === 'housesitting' ? 'selected' : ''; ?>>House Sitting</option>
                            <option value="daycare" <?php echo $serviceType === 'daycare' ? 'selected' : ''; ?>>Day Care</option>
                            <option value="walking" <?php echo $serviceType === 'walking' ? 'selected' : ''; ?>>Dog Walking</option>
                        </select>
                    </div>

                    <!-- Location Filter -->
                    <div class="filter-group">
                        <label for="address" class="filter-label">
                            <i class="fa-solid fa-location-dot"></i> Location
                        </label>
                        <input 
                            type="text" 
                            id="address" 
                            name="address" 
                            class="filter-input" 
                            placeholder="City, postcode, country..."
                            value="<?php echo htmlspecialchars($location); ?>"
                        >
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fa-solid fa-sterling-sign"></i> Price Range (per night)
                        </label>
                        <div class="price-range-inputs">
                            <input 
                                type="number" 
                                name="min_price" 
                                placeholder="Min" 
                                class="filter-input" 
                                value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" 
                                min="0"
                            >
                            <span class="price-separator">—</span>
                            <input 
                                type="number" 
                                name="max_price" 
                                placeholder="Max" 
                                class="filter-input" 
                                value="<?php echo $maxPrice < 10000 ? $maxPrice : ''; ?>" 
                                min="0"
                            >
                        </div>
                    </div>

                    <!-- Verified Only Toggle -->
                    <div class="filter-group">
                        <label class="verified-toggle">
                            <span class="verified-toggle-label">
                                <i class="fa-solid fa-shield-check"></i>
                                Verified Only
                            </span>
                            <div class="toggle-switch">
                                <input 
                                    type="checkbox" 
                                    id="star_sitter" 
                                    name="star_sitter" 
                                    value="1"
                                    <?php echo $verifiedOnly ? 'checked' : ''; ?>
                                >
                                <span class="toggle-slider"></span>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="apply-filters-btn">
                        <i class="fa-solid fa-search"></i> Apply Filters
                    </button>

                    <!-- Active Filters Display -->
                    <?php if (!empty($serviceType) || !empty($location) || $verifiedOnly || $minPrice > 0 || $maxPrice < 10000): ?>
                    <div class="active-filters">
                      <?php if (!empty($serviceType)): ?>
    <span class="filter-tag">
        <?php 
        $serviceIcons = [
            'boarding' => 'fa-home',
            'housesitting' => 'fa-house-user',
            'daycare' => 'fa-sun',
            'walking' => 'fa-dog',
            'dropin' => 'fa-clock',
            'smallpet' => 'fa-paw'
        ];
        $serviceDisplayNames = [
            'boarding' => 'Boarding',
            'housesitting' => 'House Sitting',
            'daycare' => 'Day Care',
            'walking' => 'Dog Walking',
            'dropin' => 'Drop-in Visits',
            'smallpet' => 'Small Pet Care'
        ];
        $icon = $serviceIcons[$serviceType] ?? 'fa-briefcase';
        $displayName = $serviceDisplayNames[$serviceType] ?? ucfirst($serviceType);
        ?>
        <i class="fa-solid <?php echo $icon; ?>"></i>
        <?php echo $displayName; ?>
    </span>
<?php endif; ?>
                        <?php if (!empty($location)): ?>
                            <span class="filter-tag">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($location); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($verifiedOnly): ?>
                            <span class="filter-tag">
                                <i class="fa-solid fa-shield-check"></i>
                                Verified
                            </span>
                        <?php endif; ?>
                        <?php if ($minPrice > 0 || $maxPrice < 10000): ?>
                            <span class="filter-tag">
                                <i class="fa-solid fa-sterling-sign"></i>
                                £<?php echo $minPrice; ?> - £<?php echo $maxPrice; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        <!-- Results Section -->
        <section class="results-section">
            <!-- Results Header -->
            <div class="results-header">
                <div class="results-info">
    <h2>
        <?php if (!empty($serviceType)): ?>
            <?php 
            $serviceDisplayNames = [
                'boarding' => 'Boarding',
                'housesitting' => 'House Sitting',
                'daycare' => 'Day Care',
                'walking' => 'Dog Walking',
                'dropin' => 'Drop-in Visits',
                'smallpet' => 'Small Pet Care'
            ];
            $displayName = $serviceDisplayNames[$serviceType] ?? ucfirst($serviceType);
            echo $displayName . ' Sitters';
            ?>
        <?php else: ?>
            Available Pet Sitters
        <?php endif; ?>
    </h2>
    <p class="results-count">
        Showing <strong id="total-count"><?php echo $totalSitters; ?></strong> 
        <?php 
        if (!empty($serviceType)) {
            $serviceDisplayNames = [
                'boarding' => 'boarding',
                'housesitting' => 'house sitting',
                'daycare' => 'day care',
                'walking' => 'dog walking',
                'dropin' => 'drop-in visits',
                'smallpet' => 'small pet care'
            ];
            $serviceLower = $serviceDisplayNames[$serviceType] ?? strtolower($serviceType);
            echo $totalSitters === 1 ? 'sitter' : 'sitters';
            echo ' offering <strong>' . $serviceLower . '</strong>';
        } else {
            echo $totalSitters === 1 ? 'sitter' : 'sitters';
        }
        ?>
        <?php if (!empty($location)): ?>
            near <strong><?php echo htmlspecialchars($location); ?></strong>
        <?php endif; ?>
        <?php if (!empty($allServiceNames) && empty($serviceType)): ?>
            • Services: <strong><?php echo implode(', ', array_slice($allServiceNames, 0, 3)); ?></strong>
            <?php if (count($allServiceNames) > 3): ?>
                <span style="color: #666;">+<?php echo count($allServiceNames) - 3; ?> more</span>
            <?php endif; ?>
        <?php endif; ?>
    </p>
</div>
                
                <div class="sort-dropdown">
                    <label for="sort">Sort by:</label>
                    <select id="sort" class="filter-select">
                        <option value="relevance">Relevance</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="experience">Experience</option>
                    </select>
                </div>
            </div>

            <!-- Sitters Grid -->
            <?php if (empty($sitters)): ?>
               <div class="empty-state-modern">
    <div class="empty-icon">
        <i class="fa-solid fa-search"></i>
    </div>
    <h3>No sitters found</h3>
    <p>
        We couldn't find any sitters 
        <?php if (!empty($serviceType)): ?>
            offering <strong><?php echo ucfirst($serviceType); ?></strong>
        <?php endif; ?>
        <?php if (!empty($location)): ?>
            near <strong><?php echo htmlspecialchars($location); ?></strong>
        <?php endif; ?>
        <?php if ($minPrice > 0 || $maxPrice < 10000): ?>
            within £<?php echo $minPrice; ?> - £<?php echo $maxPrice; ?> price range
        <?php endif; ?>
        . Try adjusting your filters or search area.
    </p>
    <a href="search.php" class="reset-search-btn">
        <i class="fa-solid fa-rotate-right"></i> Reset All Filters
    </a>
</div>
            <?php else: ?>
                <div class="sitters-grid">
                  
                  
                    <?php foreach ($sitters as $sitter): 
    $isVerified = $sitter['sitter_status'] === 'approved';
    $firstName = explode(' ', $sitter['fullname'])[0];
    $services = $sitter['services'] ?? [];
?>
                        <a href="sitter_profile.php?id=<?php echo $sitter['id']; ?>" 
                           class="sitter-card-modern <?php echo $isVerified ? 'verified' : ''; ?>">
                            
                            <!-- Avatar Section -->
                            <div class="sitter-avatar-section">
                                <div class="sitter-avatar-large">
                                    <?php
                                    $photoPath = $sitter['profile_photo_path'] ?? null;
                                    if ($photoPath && file_exists($photoPath)):
                                    ?>
                                        <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="<?php echo htmlspecialchars($firstName); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-large">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isVerified): ?>
                                    <div class="verified-badge-overlay">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Info Section -->
                            <div class="sitter-info-section">
                                <div class="sitter-name-row">
                                    <h3 class="sitter-name-modern"><?php echo htmlspecialchars($sitter['fullname']); ?></h3>
                                    <?php if ($isVerified): ?>
                                        <span class="status-badge verified">
                                            <i class="fa-solid fa-shield-check"></i>
                                            Verified
                                        </span>
                                    <?php elseif ($sitter['sitter_status'] === 'pending'): ?>
                                        <span class="status-badge pending">
                                            <i class="fa-solid fa-clock"></i>
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="sitter-location-modern">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>
                                        <?php echo htmlspecialchars($sitter['city'] ?? 'Location not set'); ?>, 
                                        <?php echo htmlspecialchars($sitter['country'] ?? ''); ?>
                                    </span>
                                </div>

                                <?php if (!empty($sitter['headline'])): ?>
                                    <p class="sitter-headline-modern">
                                        "<?php echo htmlspecialchars($sitter['headline']); ?>"
                                    </p>
                                <?php endif; ?>

                                <!-- Services -->
                              <!-- Services -->
<?php 
$services = $sitter['services'] ?? [];
if (!empty($services)): 
?>
    <div class="sitter-services">
        <?php 
        $serviceIcons = [
            'boarding' => 'fa-home',
            'housesitting' => 'fa-house-user',
            'daycare' => 'fa-sun',
            'walking' => 'fa-dog',
            'dropin' => 'fa-clock',
            'smallpet' => 'fa-paw'
        ];
        
        // Display each service with its price
        foreach ($services as $service): 
            $icon = $serviceIcons[$service['service_type']] ?? 'fa-paw';
            $price = isset($service['price_per_night']) ? (float)$service['price_per_night'] : 0;
        ?>
            <span class="service-tag">
                <i class="fa-solid <?php echo $icon; ?>"></i>
                <?php echo ucfirst($service['service_type']); ?>
                <?php if ($price > 0): ?>
                    <span class="service-price-tag">
                        £<?php echo number_format($price, 2); ?>
                    </span>
                <?php endif; ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
                                <!-- Features -->
                                <div class="sitter-features">
                                    <?php if (!empty($sitter['years_experience'])): ?>
                                        <div class="feature-item">
                                            <i class="fa-solid fa-award"></i>
                                            <span><?php echo $sitter['years_experience']; ?>+ years experience</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($sitter['home_type'])): ?>
                                        <div class="feature-item">
                                            <i class="fa-solid fa-house"></i>
                                            <span><?php echo htmlspecialchars($sitter['home_type']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($sitter['outdoor_space']) && $sitter['outdoor_space'] !== 'No Outdoor Space'): ?>
                                        <div class="feature-item">
                                            <i class="fa-solid fa-tree"></i>
                                            <span><?php echo htmlspecialchars($sitter['outdoor_space']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Pricing Section -->
                          <!-- Pricing Section -->
<div class="sitter-pricing-section">
    <?php 
    $services = $sitter['services'] ?? [];
    if (!empty($services)): 
        // Filter out services with price = 0
        $validPrices = array_filter(
            array_column($services, 'price_per_night'), 
            function($price) { return $price > 0; }
        );
        
        if (!empty($validPrices)):
            $minPrice = min($validPrices);
            $maxPrice = max($validPrices);
    ?>
        <div class="price-display-modern">
            <span class="price-from">From</span>
            <span class="price-amount-modern">
                £<?php echo number_format($minPrice, 2); ?>
            </span>
            <span class="price-period">per night</span>
            <?php if ($minPrice != $maxPrice): ?>
                <small style="color: #999; font-size: 0.8rem;">up to £<?php echo number_format($maxPrice, 2); ?></small>
            <?php endif; ?>
        </div>
        <?php else: ?>
            <div class="price-display-modern">
                <span class="price-period" style="color: #999;">Price on request</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>             
                                <button class="view-profile-btn" onclick="event.preventDefault(); window.location.href='sitter_profile.php?id=<?php echo $sitter['id']; ?>'">
                                    View Profile
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const serviceSelect = document.getElementById('service_type');
    const verifiedCheckbox = document.getElementById('star_sitter');
    const sortSelect = document.getElementById('sort');
    
    // Get current filter values from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentServiceType = urlParams.get('service_type') || '';
    const currentLocation = urlParams.get('address') || '';
    
    // Auto-submit on service type change
    if (serviceSelect) {
        serviceSelect.addEventListener('change', function() {
            showLoading();
            updateUIForService(this.value);
            searchForm.submit();
        });
    }
    
    // Auto-submit on verified checkbox change
    if (verifiedCheckbox) {
        verifiedCheckbox.addEventListener('change', function() {
            showLoading();
            searchForm.submit();
        });
    }
    
    // Sort functionality
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortResults(this.value);
        });
    }
    
    // Form submission with loading state
    searchForm.addEventListener('submit', function(e) {
        showLoading();
    });
    
    // Update result count on page load
updateResultCount();
    
    // Smooth scroll to results on filter change
    if (window.location.search) {
        setTimeout(() => {
            const resultsSection = document.querySelector('.results-section');
            if (resultsSection) {
                resultsSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 100);
    }
});

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('active');
    }
}

function updateResultCount() {
    const sitterCards = document.querySelectorAll('.sitter-card-modern');
    const totalCount = document.getElementById('total-count');
    
    if (totalCount) {
        totalCount.textContent = sitterCards.length;
    }
}

function updateUIForService(serviceType) {
    // This will be called before page reload to give immediate feedback
    const serviceDisplayNames = {
        'boarding': 'Boarding',
        'housesitting': 'House Sitting',
        'daycare': 'Day Care',
        'walking': 'Dog Walking',
        'dropin': 'Drop-in Visits',
        'smallpet': 'Small Pet Care'
    };
    
    const displayName = serviceDisplayNames[serviceType] || 'Available';
    
    // Update header immediately
    const resultsHeader = document.querySelector('.results-info h2');
    if (resultsHeader) {
        resultsHeader.textContent = serviceType ? displayName + ' Sitters' : 'Available Pet Sitters';
    }
}

function sortResults(sortBy) {
    const grid = document.querySelector('.sitters-grid');
    if (!grid) return;
    
    const cards = Array.from(document.querySelectorAll('.sitter-card-modern'));
    
    cards.sort((a, b) => {
        switch(sortBy) {
            case 'price-low':
                const priceA = getPriceFromCard(a);
                const priceB = getPriceFromCard(b);
                return priceA - priceB;
                
            case 'price-high':
                const priceA2 = getPriceFromCard(a);
                const priceB2 = getPriceFromCard(b);
                return priceB2 - priceA2;
                
            case 'experience':
                const expA = getExperienceFromCard(a);
                const expB = getExperienceFromCard(b);
                return expB - expA;
                
            default:
                return 0;
        }
    });
    
    // Re-append sorted cards with animation
    cards.forEach((card, index) => {
        card.style.animation = 'none';
        setTimeout(() => {
            card.style.animation = `fadeInUp 0.5s ease forwards`;
            card.style.animationDelay = `${index * 0.05}s`;
            grid.appendChild(card);
        }, 10);
    });
}

function getPriceFromCard(card) {
    const priceElement = card.querySelector('.price-amount-modern');
    if (!priceElement) return 999999; // Put cards without price at the end
    
    const priceText = priceElement.textContent.replace(/[£,]/g, '');
    const price = parseFloat(priceText);
    return isNaN(price) ? 999999 : price;
}

function getExperienceFromCard(card) {
    const expElement = card.querySelector('.feature-item:has(i.fa-award)');
    if (!expElement) return 0;
    
    const expText = expElement.textContent;
    const expMatch = expText.match(/(\d+)/);
    return expMatch ? parseInt(expMatch[1]) : 0;
}

// Prevent default on card links when clicking buttons
document.querySelectorAll('.view-profile-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const onclickAttr = this.getAttribute('onclick');
        if (onclickAttr) {
            const hrefMatch = onclickAttr.match(/href='([^']+)'/);
            if (hrefMatch) {
                window.location.href = hrefMatch[1];
            }
        }
    });
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Clear all filters
        if (confirm('Clear all filters and reset search?')) {
            window.location.href = 'search.php';
        }
    }
});

// Add visual feedback on card hover
document.querySelectorAll('.sitter-card-modern').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Price range validation
const minPriceInput = document.querySelector('input[name="min_price"]');
const maxPriceInput = document.querySelector('input[name="max_price"]');

if (minPriceInput && maxPriceInput) {
    minPriceInput.addEventListener('change', function() {
        const minVal = parseFloat(this.value) || 0;
        const maxVal = parseFloat(maxPriceInput.value) || 10000;
        
        if (minVal > maxVal) {
            maxPriceInput.value = minVal;
        }
    });
    
    maxPriceInput.addEventListener('change', function() {
        const minVal = parseFloat(minPriceInput.value) || 0;
        const maxVal = parseFloat(this.value) || 10000;
        
        if (maxVal < minVal) {
            minPriceInput.value = maxVal;
        }
    });
}

// Analytics tracking
function trackFilterChange(filterType, filterValue) {
    console.log(`Filter changed: ${filterType} = ${filterValue}`);
    
    // Track in stats
    if (window.gtag) {
        gtag('event', 'filter_change', {
            'filter_type': filterType,
            'filter_value': filterValue
        });
    }
}

// Watch for filter changes and track them
const filterInputs = document.querySelectorAll('#searchForm input, #searchForm select');
filterInputs.forEach(input => {
    input.addEventListener('change', function() {
        trackFilterChange(this.name, this.value);
    });
});

// Handle browser back/forward buttons
window.addEventListener('popstate', function(e) {
    // Reload page to reflect URL changes
    location.reload();
});

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add debounced search for location input
const locationInput = document.querySelector('input[name="address"]');
if (locationInput) {
    const debouncedSearch = debounce(() => {
        // Could add live search suggestions here
        console.log('Location search:', locationInput.value);
    }, 300);
    
    locationInput.addEventListener('input', debouncedSearch);
}
</script>

<?php include 'footer.php'; ?>