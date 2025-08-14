<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prakiraan Cuaca BMKG</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="container">
        <div class="header-content">
            <div class="header-main">
                <h1><i class="fas fa-cloud-sun"></i> Prakiraan Cuaca BMKG</h1>
                <p>Informasi cuaca terkini dari Badan Meteorologi, Klimatologi, dan Geofisika</p>
            </div>
            
            <div class="header-nav">
                <?php if (isset($is_logged_in) && $is_logged_in): ?>
                    <div class="user-info">
                        <span class="welcome-text">
                            <i class="fas fa-user"></i>
                            Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </span>
                        <div class="nav-buttons">
                            <a href="dashboard.php" class="nav-btn">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a href="auth/logout.php" class="nav-btn logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="guest-info">
                        <?php if (isset($search_limit_check) && !$search_limit_check['allowed']): ?>
                            <div class="limit-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Batas pencarian tercapai
                            </div>
                        <?php elseif (isset($search_limit_check) && $search_limit_check['remaining'] !== 'unlimited'): ?>
                            <div class="limit-info">
                                <i class="fas fa-search"></i>
                                Sisa pencarian: <?php echo $search_limit_check['remaining']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="nav-buttons">
                            <a href="auth/login.php" class="nav-btn">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<style>
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-main {
    flex: 1;
}

.header-nav {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-info, .guest-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.welcome-text {
    color: #fff;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-buttons {
    display: flex;
    gap: 0.5rem;
}

.nav-btn {
    padding: 8px 15px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-size: 0.9rem;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.logout-btn {
    background: rgba(231, 76, 60, 0.2);
    border-color: rgba(231, 76, 60, 0.3);
}

.logout-btn:hover {
    background: rgba(231, 76, 60, 0.3);
}

.limit-warning {
    background: rgba(231, 76, 60, 0.2);
    color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid rgba(231, 76, 60, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.limit-info {
    background: rgba(255, 193, 7, 0.2);
    color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid rgba(255, 193, 7, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .header-nav {
        width: 100%;
        justify-content: center;
    }
    
    .user-info, .guest-info {
        flex-direction: column;
        width: 100%;
        align-items: center;
    }
    
    .nav-buttons {
        justify-content: center;
    }
}
</style>

<div class="container">