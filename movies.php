<?php
// Prevent unwanted output
ob_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once "db_connect.php";

// Handle movie upload (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie-name'])) {
    header('Content-Type: application/json');
    $name = filter_var($_POST['movie-name'], FILTER_SANITIZE_STRING);
    $image_url = filter_var($_POST['image-url'], FILTER_SANITIZE_URL);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $movie_link = filter_var($_POST['movie-link'], FILTER_SANITIZE_URL);

    // Validate inputs
    if (empty($name) || empty($image_url) || empty($category) || empty($movie_link)) {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    // Additional validation for URLs
    if (!filter_var($image_url, FILTER_VALIDATE_URL) || !filter_var($movie_link, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid URL format"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO movies (name, image_url, category, movie_link) VALUES (:name, :image_url, :category, :movie_link)");
        $stmt->execute([
            ':name' => $name,
            ':image_url' => $image_url,
            ':category' => $category,
            ':movie_link' => $movie_link
        ]);
        echo json_encode([
            "success" => "Movie uploaded successfully",
            "movie" => [
                "name" => $name,
                "image_url" => $image_url,
                "category" => $category,
                "movie_link" => $movie_link
            ]
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to upload movie: " . $e->getMessage()]);
    }
    ob_end_flush();
    exit;
}

// Handle fetching movies (GET request with 'action' parameter)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'get_movies') {
    header('Content-Type: application/json');
    try {
        $stmt = $conn->query("SELECT * FROM movies");
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($movies);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch movies: " . $e->getMessage()]);
    }
    ob_end_flush();
    exit;
}

$conn = null;
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieStream</title>
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700;900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <style>
    /* Base styles */
    body {
      background: #181111;
      color: white;
      font-family: 'Plus Jakarta Sans', 'Noto Sans', sans-serif;
    }

    /* Hero slider */
    .slider-container {
      position: relative;
      width: 100%;
      min-height: 400px;
      overflow: hidden;
    }

    .slide {
      position: absolute;
      inset: 0;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
      background-size: cover;
      background-position: center;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
    }

    .slide.active {
      opacity: 1;
    }

    .nav-dots {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 8px;
    }

    .dot {
      width: 10px;
      height: 10px;
      background: rgba(255, 255, 255, 0.5);
      border-radius: 50%;
      cursor: pointer;
      transition: background 0.3s;
    }

    .dot.active {
      background: #e50914;
    }

    .nav-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0, 0, 0, 0.5);
      color: white;
      padding: 10px;
      cursor: pointer;
      border-radius: 50%;
      transition: background 0.3s;
    }

    .nav-arrow:hover {
      background: rgba(0, 0, 0, 0.8);
    }

    .prev {
      left: 20px;
    }

    .next {
      right: 20px;
    }

    /* Category Filters */
    .CategoryFilters {
      position: sticky;
      top: 64px;
      z-index: 9;
      background: #181111;
      display: flex;
      overflow-x: auto;
      gap: 12px;
      padding: 16px;
      scroll-behavior: smooth;
      scroll-snap-type: x mandatory;
      -ms-overflow-style: none;
      scrollbar-width: none;
      max-width: 100vw;
      box-sizing: border-box;
    }

    .CategoryFilters::-webkit-scrollbar {
      display: none;
    }

    .genre-filter {
      flex-shrink: 0;
      white-space: nowrap;
      background: #382929;
      color: white;
      font-weight: 600;
      padding: 8px 12px;
      border-radius: 4px;
      transition: background 0.3s;
      font-size: 14px;
      min-width: 80px;
      text-align: center;
      scroll-snap-align: center;
    }

    .genre-filter:hover {
      background: #e50914;
    }

    .genre-filter.selected {
      background: #e50914;
    }

    .scroll-indicator {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: #382929;
      color: white;
      padding: 8px;
      border-radius: 50%;
      cursor: pointer;
      transition: opacity 0.3s, background 0.3s;
      opacity: 0;
    }

    .scroll-indicator.active {
      opacity: 1;
    }

    .scroll-indicator:hover {
      background: #e50914;
    }

    .scroll-indicator-left {
      left: 8px;
    }

    .scroll-indicator-right {
      right: 8px;
    }

    @media (min-width: 768px) {
      .CategoryFilters {
        justify-content: space-around;
        scroll-snap-type: none;
        padding: 24px;
      }

      .scroll-indicator {
        display: none;
      }

      .genre-filter {
        font-size: 16px;
        padding: 8px 16px;
      }
    }

    /* Content sections */
    .content-below-filters {
      padding-top: 64px;
    }

    .genre-section {
      display: none;
    }

    .genre-section.active {
      display: block;
    }

    .movie-card:hover .movie-overlay {
      opacity: 1;
      transform: translateY(0);
    }

    .movie-overlay {
      opacity: 0;
      transform: translateY(100%);
      transition: all 0.3s ease-in-out;
    }

    /* Search modal */
    .modal {
      transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
  </style>
</head>

<body>
  <div class="relative flex min-h-screen flex-col">
    <!-- Header -->
    <header class="flex items-center bg-[#181111] p-4 justify-between sticky top-0 z-10 shadow-lg">
      <div class="flex items-center gap-3">
        <div class="size-10 rounded-full bg-cover" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDpGFAitzCtZcwGYakDVBeofPd673iOfjMyLAYFiiPqXV8H35BX5cxnn7autiqsO8QmT7Qw3104qSSoYSMJx6y3J6Lc5299ooNJMPsIBO5SLWoFE_I-YqATgEd2k0PxmoHrk22lvQ7XY2KaO7IE5AFsozwoFlXWruqCW3mSeYMvA117kObBRcBAie0SOdehkatSFvgpNS6Cc1qaHFB7hu30bSfGx1wKvDSSTn1dQckRTjtRZGcPJ6R8PKeTRjz4J9wvgYnFMg8C3qh2');"></div>
        <h1 class="text-2xl font-bold">MovieStream</h1>
      </div>
      <div class="flex items-center gap-3">
        <button id="search-btn" class="p-2 hover:bg-[#382929] rounded-full transition-colors" aria-label="Search movies">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
            <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
          </svg>
        </button>
        <button id="admin-btn" class="p-2 hover:bg-[#382929] rounded-full transition-colors" aria-label="Admin Panel">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
            <path d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z"></path>
          </svg>
        </button>
      </div>
    </header>

    <!-- Search Modal -->
    <div id="search-modal" class="fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center z-50">
      <div class="bg-[#261c1c] p-6 rounded-lg w-full max-w-md modal transform translate-y-4 opacity-0">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Search Movies</h2>
          <button id="close-modal" class="text-white hover:text-gray-300" aria-label="Close search modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M165.66,101.66,139.31,128l26.35,26.34a8,8,0,0,1-11.32,11.32L128,139.31l-26.34,26.35a8,8,0,0,1-11.32-11.32L116.69,128,90.34,101.66a8,8,0,0,1,11.32-11.32L128,116.69l26.34-26.35a8,8,0,1,1,11.32,11.32Z"></path>
            </svg>
          </button>
        </div>
        <input type="text" id="search-input" class="w-full p-2 rounded bg-[#382929] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e50914]" placeholder="Search for movies...">
        <div id="search-results" class="mt-4"></div>
      </div>
    </div>

    <!-- Admin Upload Modal -->
    <div id="admin-modal" class="fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center z-50">
      <div class="bg-[#261c1c] p-6 rounded-lg w-full max-w-md modal transform translate-y-4 opacity-0">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Upload Movie</h2>
          <button id="close-admin-modal" class="text-white hover:text-gray-300" aria-label="Close admin modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M165.66,101.66,139.31,128l26.35,26.34a8,8,0,0,1-11.32,11.32L128,139.31l-26.34,26.35a8,8,0,0,1-11.32-11.32L116.69,128,90.34,101.66a8,8,0,0,1,11.32-11.32L128,116.69l26.34-26.35a8,8,0,0,1,11.32,11.32Z"></path>
            </svg>
          </button>
        </div>
        <form id="upload-form" method="POST">
          <input type="text" id="movie-name" name="movie-name" placeholder="Movie Name" class="w-full p-2 mb-2 rounded bg-[#382929] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e50914]" required>
          <input type="url" id="image-url" name="image-url" placeholder="Image URL" class="w-full p-2 mb-2 rounded bg-[#382929] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e50914]" required>
          <select id="category" name="category" class="w-full p-2 mb-2 rounded bg-[#382929] text-white focus:outline-none focus:ring-2 focus:ring-[#e50914]" required>
            <option value="featured">Featured</option>
            <option value="top10">Top 10</option>
            <option value="trending">Trending</option>
            <option value="crime">Crime/Thriller</option>
            <option value="comedy">Comedy</option>
            <option value="action">Action</option>
            <option value="romance">Love Story</option>
            <option value="biopic">Biopic</option>
            <option value="southindian">South Indian</option>
            <option value="bollywood">Bollywood</option>
            <option value="hollywood">Hollywood</option>
            <option value="bengali">Bengali</option>
          </select>
          <input type="url" id="movie-link" name="movie-link" placeholder="Movie Link (e.g., https://example.com/watch)" class="w-full p-2 mb-4 rounded bg-[#382929] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e50914]" required>
          <button type="submit" class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded w-full transition-colors">Upload</button>
        </form>
      </div>
    </div>

    <!-- Hero Slider -->
    <br>
    <section class="@container">
      <div class="@[480px]:px-4 @[480px]:py-3">
        <div class="slider-container">
          <div class="slide active" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%), url('https://lh3.googleusercontent.com/aida-public/AB6AXuAKHk1XYbGoB2Hl0xqbJ-Uiz-Q1IJRpmFrpNS2rtJiWX6HtCLLsn21HhpU4ENpKHxEh2gWQO2f9-x29EsXOJgkcXChhFWKd5G5kTL2war_ixT8-6_34Oxuxo1-2DznBdXf3fNGux5m6VJIHHT4JmXfEOT9J5f3LdY7BOuR2vqmQPHvCw2zr2anfITAYWwJduxBEd9uaQsE3mq-NGPSZ6M9vbtm7PkPXC6_gzsQPuahG8EuA1AmZQhj8sg47y7qvVOR1E3Gug0V7nY3O');">
            <div class="p-6">
              <h2 class="text-3xl font-bold tracking-tight">The Last Frontier</h2>
              <p class="text-gray-300 mt-2 max-w-md">An epic sci-fi adventure exploring uncharted worlds.</p>
              <button class="mt-4 bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
            </div>
          </div>
          <div class="slide" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%), url('https://images.unsplash.com/photo-1616530940355-351fabd68c34?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
            <div class="p-6">
              <h2 class="text-3xl font-bold tracking-tight">Mystic Shadows</h2>
              <p class="text-gray-300 mt-2 max-w-md">A thrilling fantasy tale of ancient magic.</p>
              <button class="mt-4 bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
            </div>
          </div>
          <div class="slide" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%), url('https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">
            <div class="p-6">
              <h2 class="text-3xl font-bold tracking-tight">City of Echoes</h2>
              <p class="text-gray-300 mt-2 max-w-md">A dystopian drama about rebellion.</p>
              <button class="mt-4 bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
            </div>
          </div>
          <div class="nav-dots">
            <div class="dot active" data-slide="0"></div>
            <div class="dot" data-slide="1"></div>
            <div class="dot" data-slide="2"></div>
          </div>
          <div class="nav-arrow prev">❮</div>
          <div class="nav-arrow next">❯</div>
        </div>
      </div>
    </section>

    <!-- Category Filters -->
    <nav class="CategoryFilters">
      <button class="genre-filter selected" data-genre="all">All</button>
      <button class="genre-filter" data-genre="featured">Featured</button>
      <button class="genre-filter" data-genre="top10">Top 10</button>
      <button class="genre-filter" data-genre="trending">Trending</button>
      <button class="genre-filter" data-genre="crime">Crime/Thriller</button>
      <button class="genre-filter" data-genre="comedy">Comedy</button>
      <button class="genre-filter" data-genre="action">Action</button>
      <button class="genre-filter" data-genre="romance">Love Story</button>
      <button class="genre-filter" data-genre="biopic">Biopic</button>
      <button class="genre-filter" data-genre="southindian">South Indian</button>
      <button class="genre-filter" data-genre="bollywood">Bollywood</button>
      <button class="genre-filter" data-genre="hollywood">Hollywood</button>
      <button class="genre-filter" data-genre="bengali">Bengali</button>
      <div class="scroll-indicator scroll-indicator-left" aria-label="Scroll filters left">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
          <path d="M181.66,133.66l-48,48a8,8,0,0,1-11.32-11.32L156.69,136H48a8,8,0,0,1,0-16H156.69l-34.35-34.34a8,8,0,0,1,11.32-11.32l48,48A8,8,0,0,1,181.66,133.66Z"></path>
        </svg>
      </div>
      <div class="scroll-indicator scroll-indicator-right" aria-label="Scroll filters right">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
          <path d="M74.34,133.66l48,48a8,8,0,0,1-11.32,11.32L76.69,158.66H168a8,8,0,0,1,0,16H76.69l34.35-34.34a8,8,0,0,1,11.32,11.32Z"></path>
        </svg>
      </div>
    </nav>

    <!-- Content Sections -->
    <div class="content-below-filters">
      <section class="genre-section" data-genre="featured">
        <div class="flex justify-between items-center px-4 py-5">
          <h2 class="text-2xl font-bold tracking-tight">Featured</h2>
          <button class="genre-filter bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors" data-genre="featured">See More</button>
        </div>
        <div class="flex overflow-x-auto gap-4 p-4 scrollbar-thin scrollbar-thumb-[#382929] scrollbar-track-[#181111]" id="featured-preview"></div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 hidden" id="featured-all"></div>
      </section>
      <section class="genre-section active" data-genre="all">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">All Movies</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="top10">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Top 10</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="trending">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Trending</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="crime">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Crime/Thriller</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="comedy">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Comedy</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="action">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Action</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="romance">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Love Story</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="biopic">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Biopic</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="southindian">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">South Indian</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="bollywood">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Bollywood</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="hollywood">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Hollywood</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
      <section class="genre-section" data-genre="bengali">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Bengali</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4"></div>
      </section>
    </div>

    <!-- Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 bg-[#261c1c] border-t border-[#382929] p-4 flex justify-around">
      <a href="#movies.php" class="flex flex-col items-center gap-1 text-white" aria-label="Home">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
          <path d="M224,115.55V208a16,16,0,0,1-16,16H168a16,16,0,0,1-16-16V168a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v40a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V115.55a16,16,0,0,1,5.17-11.78l80-75.48.11-.11a16,16,0,0,1,21.53,0,1.14,1.14,0,0,0,.11.11l80,75.48A16,16,0,0,1,224,115.55Z"></path>
        </svg>
        <p class="text-xs font-medium">Home</p>
      </a>
      <a href="Movies_Search.php" class="flex flex-col items-center gap-1 text-[#b89d9f]" aria-label="Search">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
          <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
        </svg>
        <p class="text-xs font-medium">Search</p>
      </a>
      <a href="#" class="flex flex-col items-center gap-1 text-[#b89d9f]" aria-label="Downloads">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
          <path d="M224,152v56a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V152a8,8,0,0,1,16,0v56H208V152a8,8,0,0,1,16,0Zm-101.66,5.66a8,8,0,0,0,11.32,0l40-40a8,8,0,0,0-11.32-11.32L136,132.69V40a8,8,0,0,0-16,0v92.69L93.66,106.34a8,8,0,0,0-11.32,11.32Z"></path>
        </svg>
        <p class="text-xs font-medium">Downloads</p>
      </a>
      <a href="#" class="flex flex-col items-center gap-1 text-[#b89d9f]" aria-label="Profile">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
          <path d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z"></path>
        </svg>
        <p class="text-xs font-medium">Profile</p>
      </a>
    </nav>
  </div>

  <script>
    // Search Modal
    const searchBtn = document.getElementById('search-btn');
    const searchModal = document.getElementById('search-modal');
    const closeModal = document.getElementById('close-modal');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');

    function toggleSearchModal() {
      searchModal.classList.toggle('hidden');
      const modal = searchModal.querySelector('.modal');
      modal.classList.toggle('opacity-0');
      modal.classList.toggle('translate-y-4');
      if (!searchModal.classList.contains('hidden')) searchInput.focus();
    }

    searchBtn.addEventListener('click', toggleSearchModal);
    closeModal.addEventListener('click', toggleSearchModal);
    searchModal.addEventListener('click', e => {
      if (e.target === searchModal) toggleSearchModal();
    });

    // Fetch movies for search
    async function searchMovies(query) {
      try {
        const response = await fetch('?action=get_movies', {
          headers: { 'Accept': 'application/json' }
        });
        const movies = await response.json();
        if (response.ok) {
          return movies.filter(movie => movie.name.toLowerCase().includes(query.toLowerCase()));
        } else {
          console.error('Failed to fetch movies:', movies.error);
          return [];
        }
      } catch (error) {
        console.error('Error fetching movies:', error);
        return [];
      }
    }

    searchInput.addEventListener('input', async e => {
      const query = e.target.value;
      const results = await searchMovies(query);
      searchResults.innerHTML = results.length ?
        results.map(movie => `
          <p class="p-2 hover:bg-[#382929] cursor-pointer rounded" onclick="window.location.href='${movie.movie_link}'">${movie.name}</p>
        `).join('') :
        '<p class="text-gray-400">No results found</p>';
    });

    // Admin Modal
    const adminBtn = document.getElementById('admin-btn');
    const adminModal = document.getElementById('admin-modal');
    const closeAdminModal = document.getElementById('close-admin-modal');
    const uploadForm = document.getElementById('upload-form');

    function toggleAdminModal() {
      adminModal.classList.toggle('hidden');
      const modal = adminModal.querySelector('.modal');
      modal.classList.toggle('opacity-0');
      modal.classList.toggle('translate-y-4');
    }

    adminBtn.addEventListener('click', toggleAdminModal);
    closeAdminModal.addEventListener('click', toggleAdminModal);
    adminModal.addEventListener('click', e => {
      if (e.target === adminModal) toggleAdminModal();
    });

    // Add movie card to the page
    function addMovieCard(movie) {
      const sectionGrid = document.querySelector(`.genre-section[data-genre="${movie.category}"] .grid`);
      const allGrid = document.querySelector('.genre-section[data-genre="all"] .grid');
      const featuredPreview = document.querySelector('#featured-preview');
      const featuredAll = document.querySelector('#featured-all');

      const card = document.createElement('div');
      card.className = 'movie-card flex flex-col gap-3 relative';
      card.innerHTML = `
        <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('${movie.image_url}');">
          <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
            <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='${movie.movie_link}'">Watch Now</button>
          </div>
        </div>
        <p class="text-base font-medium">${movie.name}</p>
      `;

      // Add to category grid
      if (sectionGrid) {
        sectionGrid.appendChild(card.cloneNode(true));
      }

      // Add to All Movies grid
      if (allGrid) {
        allGrid.appendChild(card.cloneNode(true));
      }

      // Add to Featured preview and full list if category is 'featured'
      if (movie.category === 'featured') {
        if (featuredPreview && featuredPreview.children.length < 3) {
          featuredPreview.appendChild(card.cloneNode(true));
        }
        if (featuredAll) {
          featuredAll.appendChild(card.cloneNode(true));
        }
      }
    }

    uploadForm.addEventListener('submit', async e => {
      e.preventDefault();
      const name = document.getElementById('movie-name').value;
      const image = document.getElementById('image-url').value;
      const category = document.getElementById('category').value;
      const link = document.getElementById('movie-link').value;

      // Client-side validation
      if (!name || !image || !category || !link) {
        alert('Please fill all fields');
        return;
      }

      try {
        const response = await fetch('', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
          },
          body: new URLSearchParams({
            'movie-name': name,
            'image-url': image,
            'category': category,
            'movie-link': link
          })
        });

        const result = await response.json();

        if (response.ok) {
          // Add the new movie to the page
          addMovieCard(result.movie);
          uploadForm.reset();
          toggleAdminModal();
          alert(result.success || 'Movie uploaded successfully');
        } else {
          alert(result.error || 'Failed to upload movie');
        }
      } catch (error) {
        console.error('Error uploading movie:', error);
        alert('Error uploading movie: ' + error.message);
      }
    });

    // Load Movies
    async function loadMovies() {
      try {
        const response = await fetch('?action=get_movies', {
          headers: { 'Accept': 'application/json' }
        });
        const movies = await response.json();

        if (response.ok) {
          const allGrid = document.querySelector('.genre-section[data-genre="all"] .grid');
          const featuredPreview = document.querySelector('#featured-preview');
          const featuredAll = document.querySelector('#featured-all');
          const categoryGrids = {};

          // Clear existing content
          allGrid.innerHTML = '';
          featuredPreview.innerHTML = '';
          featuredAll.innerHTML = '';
          document.querySelectorAll('.genre-section:not([data-genre="all"]) .grid').forEach(grid => {
            grid.innerHTML = '';
            categoryGrids[grid.closest('.genre-section').dataset.genre] = grid;
          });

          // Populate movies
          movies.forEach((movie, index) => {
            const card = document.createElement('div');
            card.className = 'movie-card flex flex-col gap-3 relative';
            card.innerHTML = `
              <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('${movie.image_url}');">
                <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                  <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='${movie.movie_link}'">Watch Now</button>
                </div>
              </div>
              <p class="text-base font-medium">${movie.name}</p>
            `;

            allGrid.appendChild(card.cloneNode(true));

            if (categoryGrids[movie.category]) {
              categoryGrids[movie.category].appendChild(card.cloneNode(true));
            }

            if (movie.category === 'featured') {
              featuredAll.appendChild(card.cloneNode(true));
              if (index < 3) {
                featuredPreview.appendChild(card.cloneNode(true));
              }
            }
          });

          // Handle "See More" for Featured
          const seeMoreBtn = document.querySelector('.genre-filter[data-genre="featured"]');
          seeMoreBtn.addEventListener('click', () => {
            featuredPreview.classList.toggle('hidden');
            featuredAll.classList.toggle('hidden');
            setSelectedGenre(document.querySelector('.genre-filter[data-genre="featured"]'));
          });
        } else {
          console.error('Failed to load movies:', movies.error);
        }
      } catch (error) {
        console.error('Error fetching movies:', error);
      }
    }

    // Hero Slider
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevArrow = document.querySelector('.prev');
    const nextArrow = document.querySelector('.next');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
      slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
      dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
      currentSlide = index;
    }

    function startSlider() {
      slideInterval = setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
      }, 5000);
    }

    function stopSlider() {
      clearInterval(slideInterval);
    }

    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        stopSlider();
        showSlide(parseInt(dot.dataset.slide));
        startSlider();
      });
    });

    prevArrow.addEventListener('click', () => {
      stopSlider();
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      showSlide(currentSlide);
      startSlider();
    });

    nextArrow.addEventListener('click', () => {
      stopSlider();
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
      startSlider();
    });

    startSlider();

    // Category Filters
    const genreButtons = document.querySelectorAll('.genre-filter');
    const genreSections = document.querySelectorAll('.genre-section');

    function setSelectedGenre(button) {
      genreButtons.forEach(btn => {
        btn.classList.remove('selected');
        btn.classList.add('bg-[#382929]');
        btn.classList.remove('bg-[#e50914]');
      });
      button.classList.add('selected');
      button.classList.remove('bg-[#382929]');
      button.classList.add('bg-[#e50914]');

      const selectedGenre = button.dataset.genre;
      genreSections.forEach(section => {
        section.classList.toggle('active', selectedGenre === 'all' || section.dataset.genre === selectedGenre);
      });
    }

    genreButtons.forEach(button => {
      button.addEventListener('click', () => setSelectedGenre(button));
    });

    // Scroll Indicators
    const categoryFilters = document.querySelector('.CategoryFilters');
    const scrollIndicatorLeft = document.querySelector('.scroll-indicator-left');
    const scrollIndicatorRight = document.querySelector('.scroll-indicator-right');

    function updateScrollIndicators() {
      if (!categoryFilters) return;
      const isScrollable = categoryFilters.scrollWidth > categoryFilters.clientWidth;
      const atStart = categoryFilters.scrollLeft <= 0;
      const atEnd = categoryFilters.scrollLeft + categoryFilters.clientWidth >= categoryFilters.scrollWidth - 1;

      if (isScrollable) {
        scrollIndicatorLeft.classList.toggle('active', !atStart);
        scrollIndicatorRight.classList.toggle('active', !atEnd);
      } else {
        scrollIndicatorLeft.classList.remove('active');
        scrollIndicatorRight.classList.remove('active');
      }
    }

    if (categoryFilters) {
      categoryFilters.addEventListener('scroll', updateScrollIndicators);
      window.addEventListener('resize', updateScrollIndicators);
      updateScrollIndicators();

      scrollIndicatorLeft.addEventListener('click', () => {
        categoryFilters.scrollBy({
          left: -100,
          behavior: 'smooth'
        });
      });

      scrollIndicatorRight.addEventListener('click', () => {
        categoryFilters.scrollBy({
          left: 100,
          behavior: 'smooth'
        });
      });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      const firstButton = document.querySelector('.genre-filter');
      if (firstButton) setSelectedGenre(firstButton);
      loadMovies();
    });
  </script>
</body>

</html>