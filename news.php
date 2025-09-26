<?php
// Set session cookie lifetime to 6 months (180 days)
$six_months_in_seconds = 180 * 24 * 60 * 60;
session_set_cookie_params([
    'lifetime' => $six_months_in_seconds,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once "db_connect.php";

$db = Database::getInstance();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile for header
$stmt = $db->prepare("SELECT up.*, u.username FROM user_profiles up LEFT JOIN users u ON up.user_id = u.id WHERE up.user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'user_id' => $user_id,
    'username' => 'DefaultUser',
    'name' => 'Default User',
    'avatar_path' => 'Uploads/avatars/default.jpg',
    'bio' => 'No bio yet',
    'location' => 'Not specified'
];

// Sample news data - In a real application, this would come from a news API or database
$news_articles = [
    [
        'id' => 1,
        'title' => 'Artificial Intelligence Revolutionizes Healthcare: New AI Model Diagnoses Diseases with 98% Accuracy',
        'summary' => 'A breakthrough in medical AI promises to transform diagnostics worldwide. The new model outperforms human doctors in detecting rare conditions.',
        'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f',
        'source' => 'TechHealth Journal',
        'published' => '2 hours ago',
        'category' => 'Technology',
        'url' => 'https://techhealth.com/ai-healthcare-breakthrough'
    ],
    [
        'id' => 2,
        'title' => 'SpaceX Successfully Launches Latest Starship Prototype: Mars Mission Timeline Accelerated',
        'summary' => 'Elon Musk\'s SpaceX achieves another milestone with a flawless orbital test. Experts predict manned Mars missions within the decade.',
        'image' => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa',
        'source' => 'Space News Daily',
        'published' => '4 hours ago',
        'category' => 'Science',
        'url' => 'https://spacenews.com/spacex-starship-success'
    ],
    [
        'id' => 3,
        'title' => 'Climate Change Summit Reaches Historic Agreement: Global Carbon Emissions to be Halved by 2040',
        'summary' => 'World leaders commit to ambitious climate goals at the UN summit. New funding mechanisms promise support for developing nations.',
        'image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa',
        'source' => 'Global Climate Report',
        'published' => '6 hours ago',
        'category' => 'Environment',
        'url' => 'https://climate.report/un-summit-agreement'
    ],
    [
        'id' => 4,
        'title' => 'Quantum Computing Breakthrough: Google Claims First Practical Quantum Supremacy',
        'summary' => 'Google\'s quantum computer solves complex problems in seconds that would take classical supercomputers thousands of years.',
        'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b',
        'source' => 'Quantum Computing Today',
        'published' => '1 day ago',
        'category' => 'Technology',
        'url' => 'https://quantumtoday.com/google-quantum-breakthrough'
    ],
    [
        'id' => 5,
        'title' => 'Electric Vehicle Market Surpasses 20 Million Units: Tesla Maintains Lead Position',
        'summary' => 'Global EV adoption accelerates as battery technology improves and government incentives expand worldwide.',
        'image' => 'https://images.unsplash.com/photo-1502877338535-766e3a6052db',
        'source' => 'Auto Industry News',
        'published' => '1 day ago',
        'category' => 'Business',
        'url' => 'https://autonews.com/ev-market-20m'
    ],
    [
        'id' => 6,
        'title' => 'New Exoplanet Discovery: Potentially Habitable World Found 100 Light Years Away',
        'summary' => 'NASA\'s James Webb Space Telescope identifies promising candidate for extraterrestrial life in the constellation Cygnus.',
        'image' => 'https://images.unsplash.com/photo-1558583082-3851a4a1f8db',
        'source' => 'Space Exploration Magazine',
        'published' => '2 days ago',
        'category' => 'Science',
        'url' => 'https://spaceexploration.com/exoplanet-discovery'
    ],
    [
        'id' => 7,
        'title' => 'Renewable Energy Achieves Record 40% Global Share: Solar and Wind Lead Growth',
        'summary' => 'International Energy Agency reports fastest growth in clean energy adoption, with projections for majority renewable grid by 2035.',
        'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f',
        'source' => 'Renewable Energy Weekly',
        'published' => '3 days ago',
        'category' => 'Environment',
        'url' => 'https://renewableweekly.com/40-percent-renewables'
    ],
    [
        'id' => 8,
        'title' => 'AI-Powered Drug Discovery Cuts Development Time by 50%: Pharmaceutical Industry Transformed',
        'summary' => 'Machine learning algorithms revolutionize drug discovery process, promising faster and cheaper treatments for complex diseases.',
        'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56',
        'source' => 'PharmaTech News',
        'published' => '3 days ago',
        'category' => 'Health',
        'url' => 'https://pharmatech.com/ai-drug-discovery'
    ]
];

// Categories for filtering
$categories = ['All', 'Technology', 'Science', 'Environment', 'Business', 'Health', 'Space'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>News - SocialFusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1877f2;
            --primary-hover: #166fe5;
            --text-primary: #1c1e21;
            --text-secondary: #65676b;
            --border-color: #dadde1;
            --bg-primary: #f0f2f5;
            --bg-secondary: #ffffff;
            --shadow-light: 0 1px 2px rgba(0,0,0,0.1);
            --shadow-medium: 0 4px 12px rgba(0,0,0,0.15);
            --gradient-blue: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-purple: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container-fluid {
            padding: 0;
        }

        /* Header */
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 1rem;
        }

        .logo {
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 500px;
            margin: 0 1rem;
            transition: all 0.3s ease;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            background: var(--bg-primary);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: clamp(0.8rem, 2vw, 1rem);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: clamp(0.5rem, 2vw, 1rem);
        }

        .notification-btn, .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem clamp(0.5rem, 2vw, 1rem);
            border-radius: 20px;
            text-decoration: none;
            color: var(--text-primary);
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            transition: background-color 0.2s, transform 0.2s;
            touch-action: manipulation;
        }

        .notification-btn:hover, .profile-btn:hover {
            background: rgba(24, 119, 242, 0.1);
            transform: translateY(-1px);
        }

        .profile-img {
            width: clamp(28px, 8vw, 32px);
            height: clamp(28px, 8vw, 32px);
            border-radius: 50%;
            object-fit: cover;
        }

        .hamburger-menu {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-primary);
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: clamp(1rem, 5vw, 2rem) 1rem;
            min-height: calc(100vh - 200px);
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: clamp(1.5rem, 5vw, 3rem);
        }

        .page-title {
            font-size: clamp(1.8rem, 6vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--gradient-blue);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: clamp(0.9rem, 3vw, 1.1rem);
            color: var(--text-secondary);
            margin-bottom: clamp(1rem, 3vw, 2rem);
        }

        /* Category Filter */
        .category-filter {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: clamp(0.3rem, 1.5vw, 0.5rem);
            margin-bottom: clamp(1.5rem, 5vw, 3rem);
            overflow-x: auto;
            white-space: nowrap;
            padding: 0.5rem;
            -webkit-overflow-scrolling: touch;
        }

        .category-btn {
            padding: clamp(0.4rem, 2vw, 0.5rem) clamp(0.8rem, 3vw, 1.25rem);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-weight: 500;
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            touch-action: manipulation;
        }

        .category-btn:hover, .category-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: var(--shadow-light);
        }

        /* News Grid */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(clamp(280px, 30vw, 350px), 1fr));
            gap: clamp(1rem, 3vw, 2rem);
            margin-bottom: clamp(1.5rem, 5vw, 3rem);
        }

        .news-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
        }

        .news-image {
            width: 100%;
            height: clamp(150px, 25vw, 200px);
            position: relative;
            overflow: hidden;
        }

        .news-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .news-image img:hover {
            transform: scale(1.05);
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: clamp(0.5rem, 2vw, 1rem);
        }

        .news-content {
            padding: clamp(1rem, 3vw, 1.5rem);
        }

        .news-title {
            font-size: clamp(1rem, 3.5vw, 1.25rem);
            font-weight: 600;
            margin-bottom: 0.75rem;
            line-height: 1.3;
            color: var(--text-primary);
        }

        .news-title a {
            color: inherit;
            text-decoration: none;
        }

        .news-title a:hover {
            color: var(--primary-color);
        }

        .news-summary {
            color: var(--text-secondary);
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: clamp(0.75rem, 2vw, 0.85rem);
            color: var(--text-secondary);
        }

        .news-source {
            font-weight: 500;
        }

        .news-time {
            background: var(--bg-primary);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: clamp(0.7rem, 2vw, 0.8rem);
        }

        /* Featured Article */
        .featured-article {
            grid-column: 1 / -1;
            margin-bottom: clamp(1.5rem, 5vw, 3rem);
        }

        .featured-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: row;
            height: clamp(250px, 40vw, 350px);
        }

        .featured-image {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .featured-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .featured-image img:hover {
            transform: scale(1.05);
        }

        .featured-content {
            flex: 1;
            padding: clamp(1.5rem, 4vw, 2rem);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .featured-title {
            font-size: clamp(1.4rem, 4.5vw, 1.75rem);
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .featured-summary {
            color: var(--text-secondary);
            font-size: clamp(0.9rem, 3vw, 1rem);
            line-height: 1.5;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .featured-meta {
            display: flex;
            align-items: center;
            gap: clamp(0.5rem, 2vw, 1rem);
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            color: var(--text-secondary);
        }

        .category-badge {
            background: var(--gradient-blue);
            color: white;
            padding: clamp(0.2rem, 1vw, 0.25rem) clamp(0.5rem, 2vw, 0.75rem);
            border-radius: 12px;
            font-size: clamp(0.7rem, 2vw, 0.8rem);
            font-weight: 500;
        }

        /* Loading Animation */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: clamp(2rem, 10vw, 4rem);
        }

        .spinner {
            width: clamp(30px, 8vw, 40px);
            height: clamp(30px, 8vw, 40px);
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .header-content {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .search-container {
                order: 3;
                width: 100%;
                margin: 0.5rem 1rem;
            }

            .header-actions {
                flex: 1;
                justify-content: flex-end;
            }

            .featured-card {
                flex-direction: column;
                height: auto;
            }

            .featured-image {
                height: clamp(180px, 30vw, 250px);
            }

            .featured-content {
                padding: clamp(1rem, 3vw, 1.5rem);
            }
        }

        @media (max-width: 768px) {
            .hamburger-menu {
                display: block;
            }

            .header-actions {
                display: none;
            }

            .header-actions.active {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                right: 1rem;
                background: var(--bg-secondary);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 1rem;
                box-shadow: var(--shadow-medium);
                z-index: 1000;
            }

            .news-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .category-filter {
                justify-content: flex-start;
                padding-bottom: 0.5rem;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: clamp(0.5rem, 5vw, 1rem);
            }

            .page-title {
                font-size: clamp(1.5rem, 5vw, 2rem);
            }

            .news-content {
                padding: clamp(0.8rem, 3vw, 1rem);
            }

            .news-title {
                font-size: clamp(0.9rem, 3.5vw, 1.1rem);
            }

            .news-image {
                height: clamp(120px, 25vw, 160px);
            }
        }

        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-primary: #e4e6ea;
                --text-secondary: #b0b3b8;
                --border-color: #303339;
                --bg-primary: #18191a;
                --bg-secondary: #242526;
            }

            .news-card:hover, .featured-card:hover {
                box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            }

            .category-btn {
                background: var(--bg-secondary);
                color: var(--text-primary);
            }

            .category-btn:hover, .category-btn.active {
                background: var(--primary-color);
                color: white;
            }

            .news-time {
                background: var(--bg-secondary);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search news, topics, and sources..." id="newsSearch">
            </div>

            <i class="fas fa-bars hamburger-menu" id="hamburgerMenu"></i>
            <div class="header-actions" id="headerActions">
                <a href="index.php" class="notification-btn">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="#" class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="notification-badge">3</span>
                </a>
                <a href="profile.php?user_id=<?php echo $user_id; ?>" class="profile-btn">
                    <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" alt="Profile" class="profile-img">
                    <span><?php echo htmlspecialchars($profile['name'] ?: $profile['username']); ?></span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Discover</h1>
            <p class="page-subtitle">Stay informed with the latest news and trends from around the world</p>
        </div>

        <!-- Category Filter -->
        <div class="category-filter">
            <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo urlencode($category); ?>" 
                   class="category-btn <?php echo (isset($_GET['category']) && $_GET['category'] === $category) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- News Content -->
        <div class="news-grid" id="newsGrid">
            <?php
            // Filter articles by category
            $filtered_articles = $news_articles;
            if (isset($_GET['category']) && $_GET['category'] !== 'All') {
                $filtered_articles = array_filter($news_articles, function($article) {
                    return $article['category'] === $_GET['category'];
                });
            }

            // Featured article (first article)
            if (!empty($filtered_articles)): 
                $featured = array_shift($filtered_articles);
            ?>
                <article class="news-card featured-article featured-card">
                    <div class="featured-image">
                        <img 
                            src="<?php echo htmlspecialchars($featured['image']); ?>?w=800&h=400&fit=crop" 
                            srcset="<?php echo htmlspecialchars($featured['image']); ?>?w=400&h=200&fit=crop 400w, 
                                    <?php echo htmlspecialchars($featured['image']); ?>?w=800&h=400&fit=crop 800w" 
                            sizes="(max-width: 768px) 100vw, 50vw"
                            alt="<?php echo htmlspecialchars($featured['title']); ?>">
                        <div class="image-overlay">
                            <span class="category-badge"><?php echo htmlspecialchars($featured['category']); ?></span>
                        </div>
                    </div>
                    <div class="featured-content">
                        <h2 class="featured-title">
                            <a href="<?php echo htmlspecialchars($featured['url']); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo htmlspecialchars($featured['title']); ?>
                            </a>
                        </h2>
                        <p class="featured-summary"><?php echo htmlspecialchars($featured['summary']); ?></p>
                        <div class="featured-meta">
                            <span class="news-source"><?php echo htmlspecialchars($featured['source']); ?></span>
                            <span class="news-time"><?php echo $featured['published']; ?></span>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <!-- Regular articles -->
            <?php foreach ($filtered_articles as $article): ?>
                <article class="news-card">
                    <div class="news-image">
                        <img 
                            src="<?php echo htmlspecialchars($article['image']); ?>?w=600&h=300&fit=crop" 
                            srcset="<?php echo htmlspecialchars($article['image']); ?>?w=300&h=150&fit=crop 300w, 
                                    <?php echo htmlspecialchars($article['image']); ?>?w=600&h=300&fit=crop 600w" 
                            sizes="(max-width: 768px) 100vw, 33vw"
                            alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <div class="image-overlay">
                            <span class="category-badge"><?php echo htmlspecialchars($article['category']); ?></span>
                        </div>
                    </div>
                    <div class="news-content">
                        <h3 class="news-title">
                            <a href="<?php echo htmlspecialchars($article['url']); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <p class="news-summary"><?php echo htmlspecialchars($article['summary']); ?></p>
                        <div class="news-meta">
                            <span class="news-source"><?php echo htmlspecialchars($article['source']); ?></span>
                            <span class="news-time"><?php echo $article['published']; ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (empty($filtered_articles)): ?>
                <div class="loading text-center col-span-full">
                    <div class="spinner"></div>
                    <p class="mt-2 text-secondary">No articles found for this category</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Load More Button -->
        <?php if (count($filtered_articles) >= 4): ?>
            <div class="text-center">
                <button class="btn btn-outline-primary px-4 py-2 rounded-full" onclick="loadMoreNews()">
                    <i class="fas fa-chevron-down me-2"></i>
                    Load More
                </button>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-secondary text-center py-4 mt-8" style="border-top: 1px solid var(--border-color);">
        <div class="container">
            <p class="mb-0 text-secondary">&copy; 2025 SocialFusion. Stay informed, stay connected.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="socialfusion.js"></script>
    <script>
        // Hamburger menu toggle
        document.getElementById('hamburgerMenu').addEventListener('click', function() {
            const headerActions = document.getElementById('headerActions');
            headerActions.classList.toggle('active');
        });

        // Search functionality
        document.getElementById('newsSearch').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const articles = document.querySelectorAll('.news-card');
            
            articles.forEach(article => {
                const title = article.querySelector('.news-title').textContent.toLowerCase();
                const summary = article.querySelector('.news-summary')?.textContent.toLowerCase() || '';
                
                if (title.includes(query) || summary.includes(query)) {
                    article.style.display = 'block';
                } else {
                    article.style.display = 'none';
                }
            });
        });

        // Category filter functionality (client-side)
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.textContent;
                const articles = document.querySelectorAll('.news-card:not(.featured-article)');
                
                articles.forEach(article => {
                    const articleCategory = article.querySelector('.category-badge').textContent;
                    if (category === 'All' || category === articleCategory) {
                        article.style.display = 'block';
                    } else {
                        article.style.display = 'none';
                    }
                });
                
                const featuredArticle = document.querySelector('.featured-article');
                if (featuredArticle) {
                    const featuredCategory = featuredArticle.querySelector('.category-badge').textContent;
                    if (category === 'All' || category === featuredCategory) {
                        featuredArticle.style.display = 'block';
                    } else {
                        featuredArticle.style.display = 'none';
                    }
                }
            });
        });

        // Load more functionality (placeholder)
        function loadMoreNews() {
            const btn = event.target;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-chevron-down me-2"></i>Load More';
                btn.disabled = false;
                Swal.fire({
                    title: 'Coming Soon!',
                    text: 'Infinite scroll and real-time news updates will be available in the next update.',
                    icon: 'info',
                    confirmButtonText: 'Got it!'
                });
            }, 1500);
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Lazy loading images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src], img:not([src])').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.news-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>