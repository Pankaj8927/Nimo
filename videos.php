<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialFusion Feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #0d1a26; /* Dark background for 3D effect */
            color: #fff;
            position: relative;
            overflow-x: hidden;
        }

        /* 3D Background Canvas */
        #three-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.3;
        }

        /* Sticky Header */
        .sticky-header {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333333;
            padding: 10px 15px;
        }

        #navbarNav {
            flex-grow: 1;
            justify-content: center;
        }

        .nav-item {
            margin: 0 15px;
        }

        .nav-link {
            color: #444444;
            font-size: 0.95rem;
            padding: 10px 15px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #0056b3;
        }

        .nav-link i {
            margin-right: 8px;
            font-size: 1rem;
        }

        /* News Section */
        .news-section {
            background-color: transparent;
            padding: 20px;
            display: none;
        }

        .news-section h2 {
            font-size: 2rem;
            color: #1e90ff;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }

        .news-section h2::after {
            content: '';
            width: 100px;
            height: 4px;
            background: #ff007a;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* News Card */
        .news-card {
            background: #1c2b3a;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.5s;
            position: relative;
            perspective: 1000px;
            margin-bottom: 20px;
        }

        .news-card:hover {
            transform: translateY(-10px) scale(1.05);
        }

        .news-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .news-card:hover .news-card-inner {
            transform: rotateY(180deg);
        }

        .news-card-front, .news-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 15px;
        }

        .news-card-front {
            background: #1c2b3a;
        }

        .news-card-back {
            background: linear-gradient(45deg, #ff007a, #1e90ff);
            color: #fff;
            transform: rotateY(180deg);
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid #ff007a;
        }

        .news-card .card-body {
            padding: 20px;
        }

        .news-card .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
        }

        .news-card .card-text {
            font-size: 0.9rem;
            color: #d1d4d8;
        }

        .news-card .source {
            font-size: 0.8rem;
            color: #1e90ff;
        }

        .news-card .date {
            font-size: 0.8rem;
            color: #ff007a;
        }

        /* Video Card */
        .video-card {
            background: #1c2b3a;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.5s;
            margin-bottom: 20px;
        }

        .video-card:hover {
            transform: scale(1.05);
        }

        .video-card iframe {
            width: 100%;
            height: 200px;
            border: none;
        }

        /* Navigation Tabs */
        .nav-tabs {
            border-bottom: none;
            justify-content: center;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            color: #fff;
            background: #1c2b3a;
            border: none;
            border-radius: 10px;
            margin: 0 10px;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover,
        .nav-tabs .nav-link.active {
            background: #ff007a;
            color: #fff;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 0, 122, 0.5);
        }

        /* Default Content */
        #defaultContent {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            min-height: 400px;
            color: #333;
        }

        /* Error Message */
        .alert {
            border-radius: 10px;
            background: #ff4d4d;
            color: #fff;
            text-align: center;
        }

        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            #navbarNav {
                display: flex !important;
                flex-direction: row !important;
                justify-content: center !important;
            }

            .nav-item {
                margin: 0 10px;
            }

            .nav-link {
                padding: 8px 10px;
                font-size: 0.9rem;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }

            .news-card img,
            .video-card iframe {
                height: 150px;
            }

            .news-card .card-title {
                font-size: 1rem;
            }

            .news-card .card-text {
                font-size: 0.8rem;
            }

            #defaultContent {
                min-height: 300px;
            }
        }

        /* Main Content Offset */
        .main-content {
            padding-top: 60px;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <!-- 3D Background Canvas -->
        <canvas id="three-canvas"></canvas>

        <!-- Sticky Header -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top sticky-header">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">SocialFusion</a>
                <div class="navbar-nav mx-auto" id="navbarNav">
                    <div class="nav-item">
                        <a class="nav-link" href="./news.php"><i class="fas fa-newspaper"></i> News</a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-video"></i> Video</a>
                    </div>
                    <div class="nav-item">
                        <a class="link" href="./movies.php"><i class="fas fa-film"></i> Movie</a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-play"></i> Shorts</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="row g-0 main-content mt-5">
            <!-- News Section -->
            <div class="col-12 p-3 news-section" id="news">
                <ul class="nav nav-tabs" id="newsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="today-tab" data-bs-toggle="tab" href="#today" role="tab">Today's News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="indian-tab" data-bs-toggle="tab" href="#indian" role="tab">Indian News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="global-tab" data-bs-toggle="tab" href="#global" role="tab">Global News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="videos-tab" data-bs-toggle="tab" href="#videos" role="tab">News Videos</a>
                    </li>
                </ul>

                <div class="tab-content" id="newsTabContent">
                    <!-- Today's News -->
                    <div class="tab-pane fade show active news-section" id="today" role="tabpanel">
                        <h2>Today's News</h2>
                        <div class="row g-3" id="todayContainer"></div>
                    </div>
                    <!-- Indian News -->
                    <div class="tab-pane fade news-section" id="indian" role="tabpanel">
                        <h2>Indian News</h2>
                        <div class="row g-3" id="indianContainer"></div>
                    </div>
                    <!-- Global News -->
                    <div class="tab-pane fade news-section" id="global" role="tabpanel">
                        <h2>Global News</h2>
                        <div class="row g-3" id="globalContainer"></div>
                    </div>
                    <!-- News Videos -->
                    <div class="tab-pane fade news-section" id="videos" role="tabpanel">
                        <h2>News Videos</h2>
                        <div class="row g-3" id="videosContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Default Content -->
            <div class="col-12 p-3 text-center" id="defaultContent">
                <p>Select a category from the header to view content.</p>
            </div>
        </div>

        <!-- Mobile Footer Navbar -->
        <nav class="navbar fixed-bottom navbar-light bg-light d-md-none mobile-footer-navbar">
            <div class="container-fluid justify-content-around align-items-center">
                <a href="./index.php" class="nav-link text-center">
                    <i class="fas fa-home fa-lg"></i>
                    <span class="d-block small">Home</span>
                </a>
                <a href="./videos.php" class="nav-link text-center">
                    <i class="fas fa-video fa-lg"></i>
                    <span class="d-block small">Videos</span>
                </a>
                <a href="#" class="nav-link text-center post-icon" data-bs-toggle="modal" data-bs-target="#postModal">
                    <i class="fas fa-plus-circle fa-2x text-primary"></i>
                    <span class="d-block small">Post</span>
                </a>
                <a href="#" class="nav-link text-center" data-bs-toggle="modal" data-bs-target="#friendsModal">
                    <i class="fas fa-users fa-lg"></i>
                    <span class="d-block small">Friends</span>
                </a>
                <a href="./messenger.php" class="nav-link text-center">
                    <i class="fas fa-envelope fa-lg"></i>
                    <span class="d-block small">Messages</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script>
        // Three.js 3D Background
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('three-canvas'), alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);

        const particlesGeometry = new THREE.BufferGeometry();
        const particlesCount = 5000;
        const posArray = new Float32Array(particlesCount * 3);

        for (let i = 0; i < particlesCount * 3; i++) {
            posArray[i] = (Math.random() - 0.5) * 200;
        }

        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
        const particlesMaterial = new THREE.PointsMaterial({
            size: 0.5,
            color: 0x1e90ff,
            transparent: true,
            opacity: 0.6
        });

        const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
        scene.add(particlesMesh);

        camera.position.z = 50;

        function animate() {
            requestAnimationFrame(animate);
            particlesMesh.rotation.y += 0.001;
            renderer.render(scene, camera);
        }

        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // News Fetching and Display
        document.addEventListener('DOMContentLoaded', () => {
            const newsSection = document.getElementById('news');
            const defaultContent = document.getElementById('defaultContent');
            const navLinks = document.querySelectorAll('.nav-link');
            const apiKey = 'abc123def456ghi789'; // Your actual key// Replace with your NewsAPI key

            // Function to fetch news
            async function fetchNews(endpoint, params) {
                const baseUrl = 'https://newsapi.org/v2/';
                const query = new URLSearchParams({ ...params, apiKey }).toString();
                const url = `${baseUrl}${endpoint}?${query}`;

                try {
                    const response = await fetch(url, {
                        headers: { 'User-Agent': 'NewsApp/1.0' }
                    });
                    const data = await response.json();
                    if (data.status === 'ok') {
                        return data.articles;
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error('Error fetching news:', error);
                    return null;
                }
            }

            // Function to display news articles
            function displayNews(articles, containerId) {
                const container = document.getElementById(containerId);
                container.innerHTML = articles ? '' : '<p class="text-center">Error fetching news. Please try again later.</p>';

                if (articles && articles.length > 0) {
                    articles.slice(0, 6).forEach(article => {
                        const col = document.createElement('div');
                        col.classList.add('col-md-4', 'col-sm-6');
                        col.innerHTML = `
                            <div class="news-card">
                                <div class="news-card-inner">
                                    <div class="news-card-front">
                                        <img src="${article.urlToImage || 'https://via.placeholder.com/300x200'}" alt="${article.title}">
                                        <div class="card-body">
                                            <h5 class="card-title">${article.title.length > 50 ? article.title.substring(0, 50) + '...' : article.title}</h5>
                                            <p class="card-text">${article.description ? (article.description.length > 100 ? article.description.substring(0, 100) + '...' : article.description) : 'No description available.'}</p>
                                            <p class="source">Source: ${article.source.name}</p>
                                            <p class="date">${new Date(article.publishedAt).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                            <a href="${article.url}" target="_blank" class="btn btn-primary btn-sm">Read More</a>
                                        </div>
                                    </div>
                                    <div class="news-card-back">
                                        <p>${article.description || 'Click to read more about this news.'}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.appendChild(col);
                    });
                } else if (articles) {
                    container.innerHTML = '<p class="text-center">No news available at the moment.</p>';
                }
            }

            // Function to display video news
            function displayVideoNews(articles, containerId) {
                const container = document.getElementById(containerId);
                container.innerHTML = articles ? '' : '<p class="text-center">Error fetching video news. Please try again later.</p>';

                if (articles && articles.length > 0) {
                    articles.slice(0, 4).forEach(article => {
                        const col = document.createElement('div');
                        col.classList.add('col-md-6');
                        // Placeholder YouTube video (replace with actual video URLs if available)
                        const videoUrl = 'https://www.youtube.com/embed/dQw4w9WgXcQ'; // Example video
                        col.innerHTML = `
                            <div class="video-card">
                                <iframe src="${videoUrl}" allowfullscreen></iframe>
                                <div class="card-body">
                                    <h5 class="card-title">${article.title.length > 50 ? article.title.substring(0, 50) + '...' : article.title}</h5>
                                    <p class="card-text">${article.description ? (article.description.length > 100 ? article.description.substring(0, 100) + '...' : article.description) : 'No description available.'}</p>
                                    <p class="source">Source: ${article.source.name}</p>
                                    <p class="date">${new Date(article.publishedAt).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                                    <a href="${article.url}" target="_blank" class="btn btn-primary btn-sm">Watch Full Video</a>
                                </div>
                            </div>
                        `;
                        container.appendChild(col);
                    });
                } else if (articles) {
                    container.innerHTML = '<p class="text-center">No video news available at the moment.</p>';
                }
            }

            // Function to load all news categories
            async function loadNews() {
                // Today's News
                const todaysNews = await fetchNews('top-headlines', {
                    language: 'en',
                    pageSize: 10,
                    from: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                    to: new Date().toISOString().split('T')[0]
                });
                displayNews(todaysNews, 'todayContainer');

                // Indian News
                const indianNews = await fetchNews('top-headlines', {
                    country: 'in',
                    pageSize: 10
                });
                displayNews(indianNews, 'indianContainer');

                // Global News
                const globalNews = await fetchNews('everything', {
                    q: 'world',
                    language: 'en',
                    pageSize: 10,
                    sortBy: 'publishedAt'
                });
                displayNews(globalNews, 'globalContainer');

                // Video News
                const videoNews = await fetchNews('everything', {
                    q: 'news video',
                    language: 'en',
                    pageSize: 5,
                    sortBy: 'relevancy'
                });
                displayVideoNews(videoNews, 'videosContainer');
            }

            // Handle navigation clicks
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sectionId = link.getAttribute('href').substring(1);

                    // Remove active class from all links
                    navLinks.forEach(nav => nav.classList.remove('active'));
                    // Add active class to clicked link
                    link.classList.add('active');

                    // Hide all sections
                    newsSection.style.display = 'none';
                    defaultContent.style.display = 'none';

                    // Show the selected section
                    if (sectionId === 'newsContainer') {
                        newsSection.style.display = 'block';
                        loadNews(); // Fetch and display news
                    } else {
                        defaultContent.style.display = 'block';
                    }
                });
            });

            // Load default content on page load
            defaultContent.style.display = 'block';
        });

        // Handle like button click (placeholder for future use)
        function handleLike(button) {
            const icon = button.querySelector('i');
            const countText = button.textContent.trim().split(' ')[1];
            let likeCount = parseInt(countText) || 0;

            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-danger');
                likeCount++;
            } else {
                icon.classList.remove('fas', 'text-danger');
                icon.classList.add('far');
                likeCount--;
            }

            button.innerHTML = `<i class="${icon.className}"></i> ${likeCount}`;
        }
    </script>
</body>
</html>