<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Instagram Reels Clone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body,
    html {
      margin: 0;
      padding: 0;
      scroll-behavior: smooth;
      overflow-y: scroll;
    }

    .reels-container {
      scroll-snap-type: y mandatory;
      height: 100vh;
      overflow-y: scroll;
    }

    .reel {
      scroll-snap-align: start;
    }
  </style>
</head>

<body class="bg-black text-white">

  <div class="reels-container">

    <!-- Reel 1 -->
    <div class="relative h-screen reel">
      <video class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline>
        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4" />
      </video>
      <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/10"></div>

      <!-- Profile Info -->
      <div class="absolute top-4 left-4 z-10">
        <div class="flex items-center gap-2 mb-2">
          <img src="https://i.pravatar.cc/150?img=5" class="w-9 h-9 rounded-full border border-white">
          <span class="font-semibold text-sm">@urban.legend</span>
          <button class="text-xs bg-white text-black px-2 py-0.5 rounded">Follow</button>
        </div>
      </div>
      <!-- Bottom Left Caption -->
      <div class="absolute bottom-24 left-4 z-10 max-w-[75%]">
        <p class="text-sm">🏞️ Exploring wild paths. <span class="text-blue-400">#mountains #explorer</span></p>
      </div>

      <!-- Action Icons -->
      <div class="absolute bottom-24 right-4 flex flex-col items-center space-y-5 z-10">
        <div class="text-center">
          ❤️<br><span class="text-xs">12.4k</span>
        </div>
        <div class="text-center">
          💬<br><span class="text-xs">254</span>
        </div>
        <div class="text-center">
          📤<br><span class="text-xs">Share</span>
        </div>
      </div>
    </div>

    <!-- Reel 2 -->
    <div class="relative h-screen reel">
      <video class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline>
        <source src="https://www.w3schools.com/html/movie.mp4" type="video/mp4" />
      </video>
      <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/10"></div>

      <!-- Profile Info -->
     <div class="absolute top-4 left-4 z-10">
        <div class="flex items-center gap-2 mb-2">
          <img src="https://i.pravatar.cc/150?img=5" class="w-9 h-9 rounded-full border border-white">
          <span class="font-semibold text-sm">@urban.legend</span>
          <button class="text-xs bg-white text-black px-2 py-0.5 rounded">Follow</button>
        </div>
      </div>
      <!-- Bottom Left Caption -->
      <div class="absolute bottom-24 left-4 z-10 max-w-[75%]">
        <p class="text-sm">🏞️ Exploring wild paths. <span class="text-blue-400">#mountains #explorer</span></p>
      </div>


      <!-- Action Icons -->
      <div class="absolute bottom-24 right-4 flex flex-col items-center space-y-5 z-10">
        <div class="text-center">
          ❤️<br><span class="text-xs">8.1k</span>
        </div>
        <div class="text-center">
          💬<br><span class="text-xs">431</span>
        </div>
        <div class="text-center">
          📤<br><span class="text-xs">Send</span>
        </div>
      </div>
    </div>

    <!-- Reel 3 -->
    <div class="relative h-screen reel">
      <video class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline>
        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4" />
      </video>
      <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/10"></div>

      <!-- Profile Info -->
      <!-- Top Left Profile Info -->
      <div class="absolute top-4 left-4 z-10">
        <div class="flex items-center gap-2 mb-2">
          <img src="https://i.pravatar.cc/150?img=5" class="w-9 h-9 rounded-full border border-white">
          <span class="font-semibold text-sm">@urban.legend</span>
          <button class="text-xs bg-white text-black px-2 py-0.5 rounded">Follow</button>
        </div>
      </div>
      <!-- Bottom Left Caption -->
      <div class="absolute bottom-24 left-4 z-10 max-w-[75%]">
        <p class="text-sm">🏞️ Exploring wild paths. <span class="text-blue-400">#mountains #explorer</span></p>
      </div>
      <!-- Action Icons -->
      <div class="absolute bottom-24 right-4 flex flex-col items-center space-y-5 z-10">
        <div class="text-center">
          ❤️<br><span class="text-xs">5.7k</span>
        </div>
        <div class="text-center">
          💬<br><span class="text-xs">122</span>
        </div>
        <div class="text-center">
          📤<br><span class="text-xs">DM</span>
        </div>
      </div>
    </div>

  </div>

</body>

</html>