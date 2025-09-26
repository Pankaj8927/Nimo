<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stitch Design</title>
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700;900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <style>
    body {
      background: #181111;
      color: white;
      font-family: 'Plus Jakarta Sans', 'Noto Sans', sans-serif;
    }

    /* Hide scrollbars for cast section */
    .cast-scroll::-webkit-scrollbar {
      display: none;
    }

    .cast-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    /* Ensure buttons are focusable */
    button:focus {
      outline: 2px solid #e92932;
      outline-offset: 2px;
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

    /* Video Player Styles */
    .video-player-container {
      display: none;
      padding: 16px;
      background: #181111;
    }

    .video-player-container.active {
      display: block;
    }

    .video-player {
      width: 100%;
      max-width: 1200px;
      aspect-ratio: 16 / 9;
      background: #000;
      border-radius: 8px;
      overflow: hidden;
      margin: 0 auto;
      display: block;
    }

    .player-controls {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      max-width: 1200px;
      margin: 8px auto 0;
    }

    .close-player,
    .fullscreen-button,
    .download-button {
      background: #382929;
      color: white;
      padding: 8px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 0.3s;
      width: 40px;
      height: 40px;
    }

    .close-player:hover,
    .fullscreen-button:hover,
    .download-button:hover {
      background: #e50914;
    }

    .player-error {
      display: none;
      color: #e92932;
      text-align: center;
      padding: 16px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .player-error.active {
      display: block;
    }

    .movie-details {
      display: block;
    }

    .movie-details.hidden {
      display: none;
    }

    .hero-image {
      display: block;
    }

    .hero-image.hidden {
      display: none;
    }

    .movie-description {
      text-align: center;
      max-width: 1200px;
      margin: 0 auto;
    }
  </style>
</head>

<body>
  <div class="relative flex min-h-screen flex-col bg-[#181111]">
    <!-- Header (unchanged) -->
    <header class="flex items-center bg-[#181111] p-4 pb-2 justify-between sticky top-0 z-10">
      <button class="flex size-12 shrink-0 items-center justify-center text-white hover:bg-[#382929] rounded-full transition-colors" onclick="history.back()" aria-label="Go back" role="button" tabindex="0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
          <path d="M224,128a8,8,0,0,1-8,8H59.31l58.35,58.34a8,8,0,0,1-11.32,11.32l-72-72a8,8,0,0,1,0-11.32l72-72a8,8,0,0,1,11.32,11.32L59.31,120H216A8,8,0,0,1,224,128Z"></path>
        </svg>
      </button>
      <button class="flex size-12 items-center justify-center text-white hover:bg-[#382929] rounded-full transition-colors" aria-label="Bookmark movie" role="button" tabindex="0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
          <path d="M184,32H72A16,16,0,0,0,56,48V224a8,8,0,0,0,12.24,6.78L128,193.43l59.77,37.35A8,8,0,0,0,200,224V48A16,16,0,0,0,184,32Zm0,177.57-51.77-32.35a8,8,0,0,0-8.48,0L72,209.57V48H184Z"></path>
        </svg>
      </button>
    </header>

    <!-- Hero Image (unchanged) -->
    <div class="hero-image">
      <section class="@container">
        <div class="@[480px]:px-4 @[480px]:py-3">
          <div class="w-full bg-center bg-cover flex flex-col justify-end overflow-hidden @[480px]:rounded-lg min-h-[20rem] aspect-[16/9]" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCEcVvndR-8BNFSdZH3VojUsFDDDArJ-x-naMqjz8Fk6fRejZpjN7STvQgfZnBKQKiI4FnDXx9VzvqTg5k26DSqBypyq60J0-w_oVg_3RauQKACN5hax8ibb8jivBg-REFwhU0M1erT_r5IYLlacnnH6TtOx0Tma0oU2euUzmuSWqrawR-SmPeUu7ZW-gw7dIg_gT7AWpJ5lDyCNDO3wI00y_7PhS89-RFrEs16V4uiiAEivEzdrTNYUuc6lhb4br1mWFpkUdP8Cjs8');"></div>
        </div>
      </section>
    </div>

    <!-- Movie Details (unchanged except for watch-now button data-link) -->
    <div class="movie-details">
      <h1 class="text-white text-xl @[480px]:text-2xl font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">The Silent Echo</h1>
      <p class="text-white text-sm @[480px]:text-base font-normal leading-normal pb-3 pt-1 px-4">
        A young woman discovers a hidden world within her dreams, where she must confront her deepest fears to save her family.
      </p>
      <div class="flex justify-stretch px-4 py-3">
        <div class="flex flex-1 gap-3 flex-wrap justify-between">
          <button class="watch-now flex min-w-[100px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#e92932] text-white text-sm font-bold leading-normal tracking-[0.015em]" data-link="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4" data-download="https://drive.google.com/uc?export=download&id=1W1NhHebv5-XSfRpZHuVYc5cdcr2h8C_T">
            <span class="truncate">Watch Now</span>
          </button>
          <button class="flex min-w-[100px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#382929] text-white text-sm font-bold leading-normal tracking-[0.015em]">
            <span class="truncate">Add to Watchlist</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Video Player Container -->
    <div class="video-player-container">
      <div class="relative">
        <button class="close-player absolute top-4 right-4 z-10" aria-label="Close video player">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
            <path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"></path>
          </svg>
        </button>
        <video class="video-player" controls autoplay>
          <source src="" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <p class="player-error">Unable to load video. Please try again later or check the video link.</p>
      </div>
      <div class="player-controls">
        <button class="fullscreen-button" aria-label="Toggle full screen">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
            <path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM88,192H56a8,8,0,0,1-8-8V152a8,8,0,0,1,16,0v24H88a8,8,0,0,1,0,16Zm0-112H64V104a8,8,0,0,1-16,0V72a8,8,0,0,1,8-8H88a8,8,0,0,1,0,16ZM200,192H168a8,8,0,0,1,0-16h24V152a8,8,0,0,1,16,0v32A8,8,0,0,1,200,192Zm0-112H168a8,8,0,0,1,0-16h32a8,8,0,0,1,8,8v32a8,8,0,0,1-16,0Z"></path>
          </svg>
        </button>
        <button class="download-button" aria-label="Download video" data-download="https://drive.google.com/uc?export=download&id=1W1NhHebv5-XSfRpZHuVYc5cdcr2h8C_T">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
            <path d="M224,152v56a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V152a8,8,0,0,1,16,0v56H208V152a8,8,0,0,1,16,0Zm-101.66,5.66a8,8,0,0,0,11.32,0l40-40a8,8,0,0,0-11.32-11.32L136,132.69V40a8,8,0,0,0-16,0v92.69L93.66,106.34a8,8,0,0,0-11.32,11.32Z"></path>
          </svg>
        </button>
      </div>
      <h1 class="text-white text-xl @[480px]:text-2xl font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">The Silent Echo</h1>
      <p class="movie-description text-white text-sm @[480px]:text-base font-normal leading-normal pb-3 pt-4 px-4">
        A young woman discovers a hidden world within her dreams, where she must confront her deepest fears to save her family.
      </p>
    </div>

    <!-- Category Filters (unchanged) -->
    <nav class="CategoryFilters">
      <button class="genre-filter selected" data-genre="all">All</button>
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
    </nav>
    <!-- Featured Section -->
    <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Featured</h2>
    <div class="flex overflow-x-auto gap-4 p-4 scrollbar-thin scrollbar-thumb-[#382929] scrollbar-track-[#181111]">
      <div class="movie-card flex flex-col gap-3 min-w-[200px] relative">
        <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAN8dnftzBVq605UHKPM3r5zQyg5Xlv10R3uLe38-GCet4Ja42VIwfhbxsttR2-HwGewahDnUOu3jnYOzNLWA-hUYoWpzlcm0RDMvscZ9wdJ4wVXjxeqi3-pt0oJu8v6IERqFfHh9pjfvH77lknQneJBOFZ2Rcug0Rl7oxSCJfb9AzyFDEDByFr6BkoFvfUNwliW4ioNfa5U1qAR7bANxTCRUXMCHazeGl_7QTGBg1pwjCX8_N0wTDgOSVxw_deka-E2vBC2z339a7C');">
          <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
            <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
          </div>
        </div>
        <p class="text-base font-medium">The Silent Echo</p>
      </div>
      <div class="movie-card flex flex-col gap-3 min-w-[200px] relative">
        <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDP1m_9zvRXYksciUtGz3aGM9EgWmjOHzmp6Ef_iusg9xrqFYJv_2MZS4LfaRRjXrKz346JzWnNzEfFWNVKaWmynu--7qU8A3xHY4UUCFoehHPp0s5kgs2uOWO-4e1M7-sDUALE2S5ZjHPyYZhKZR89gATUkcfyLojbnbza3rANHaVYY_mH5amy2yWJZGGuO4wacxGZLBdMHvBgtvlNu-RrfO7TNkUzjJ0tcO8sjQao6mr97k1x1jYYPmgx9mOho_90b-iguz9KLbaq');">
          <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
            <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
          </div>
        </div>
        <p class="text-base font-medium">Crimson Tide</p>
      </div>
      <div class="movie-card flex flex-col gap-3 min-w-[200px] relative">
        <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC6n1_i8Xkk8nj65ezS8HHn5Q7aooTzRaQVERDX5pYQpu3c1Kl_NLAJvDAVWkY73k4bRMNMA__3mE94z9UVzw8GTg2jg2vrAfS-a2xdgUlCmp1SRsqlLORpYsgwdjCh9F4QUr22101kev7XA16MpFN9nhWvDYEBP0BLAsdjEqfVkz9BmmHncqekq2qNSXLezr6gDfRdnuS0FoP_mTuptqYDU7HNoG23nq1a6MwxSu8HWDZSSuboJDWvWXPne_bPwDMJOY-yTgIVx8lc');">
          <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
            <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
          </div>
        </div>
        <p class="text-base font-medium">Whispers of the Past</p>
      </div>
    </div>
    <!-- Genre Sections -->
    <div class="content-below-filters">
      <section class="genre-section active" data-genre="all">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">All Movies</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">The Silent Echo</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>

        </div>
      </section>
      <section class="genre-section" data-genre="top10">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Top 10</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="trending">
        <!-- Trending -->
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Trending</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
        <!-- trending end -->
      </section>
      <section class="genre-section" data-genre="comedy">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Comedy</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="action">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Action</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="romance">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Love Story</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="southindian">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">South Indian</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="bollywood">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Bollywood</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="hollywood">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Hollywood</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="genre-section" data-genre="bengali">
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 ">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Footer Spacer -->
    <!-- Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 bg-[#261c1c] border-t border-[#382929] p-4 flex justify-around">
      <a href="./movies.php" class="flex flex-col items-center gap-1 text-white" aria-label="Home">
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
    // Watch Now Button
    const watchNowButtons = document.querySelectorAll('.watch-now');
    const videoPlayerContainer = document.querySelector('.video-player-container');
    const videoPlayer = document.querySelector('.video-player');
    const closePlayerButton = document.querySelector('.close-player');
    const playerError = document.querySelector('.player-error');
    const movieDetails = document.querySelector('.movie-details');
    const heroImage = document.querySelector('.hero-image');
    const downloadButton = document.querySelector('.download-button');

    watchNowButtons.forEach(button => {
      button.addEventListener('click', () => {
        const link = button.dataset.link;
        const downloadLink = button.dataset.download;
        if (link && videoPlayerContainer && videoPlayer && movieDetails && heroImage) {
          videoPlayer.src = link;
          videoPlayerContainer.classList.add('active');
          movieDetails.classList.add('hidden');
          heroImage.classList.add('hidden');
          playerError.classList.remove('active');
          // Update download button's data-download attribute
          if (downloadButton) {
            downloadButton.setAttribute('data-download', downloadLink);
          }
          // Scroll to player
          videoPlayerContainer.scrollIntoView({
            behavior: 'smooth'
          });
        }
      });
    });

    // Close Player
    if (closePlayerButton) {
      closePlayerButton.addEventListener('click', () => {
        if (videoPlayerContainer && videoPlayer && movieDetails && heroImage) {
          videoPlayerContainer.classList.remove('active');
          movieDetails.classList.remove('hidden');
          heroImage.classList.remove('hidden');
          videoPlayer.src = ''; // Reset video source
        }
      });
    }

    // Handle video load errors
    if (videoPlayer) {
      videoPlayer.addEventListener('error', () => {
        playerError.classList.add('active');
        videoPlayer.style.display = 'none';
      });
      videoPlayer.addEventListener('canplay', () => {
        playerError.classList.remove('active');
        videoPlayer.style.display = 'block';
      });
    }

    // Download Button
    if (downloadButton) {
      downloadButton.addEventListener('click', () => {
        const downloadLink = downloadButton.getAttribute('data-download');
        if (downloadLink) {
          // Create a temporary anchor element to trigger the download
          const a = document.createElement('a');
          a.href = downloadLink;
          a.download = ''; // Let the browser determine the filename
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        } else {
          alert('Download link not available.');
        }
      });
    }

    // Category Filters (unchanged)
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

    // Initialize with 'All' selected
    document.addEventListener('DOMContentLoaded', () => {
      const firstButton = document.querySelector('.genre-filter');
      if (firstButton) setSelectedGenre(firstButton);
    });

    // Scroll Indicators (unchanged)
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
  </script>
</body>

</html>