<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieStream</title>
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;700;900&amp;family=Plus+Jakarta+Sans:wght@400;500;700;800&amp;display=swap">
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
      /* Adjust based on header height */
      z-index: 9;
      /* Below header (z-10) */
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
      /* Prevent overlap with sticky filters */
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
  <style>
    *,
    ::before,
    ::after {
      --tw-border-spacing-x: 0;
      --tw-border-spacing-y: 0;
      --tw-translate-x: 0;
      --tw-translate-y: 0;
      --tw-rotate: 0;
      --tw-skew-x: 0;
      --tw-skew-y: 0;
      --tw-scale-x: 1;
      --tw-scale-y: 1;
      --tw-pan-x: ;
      --tw-pan-y: ;
      --tw-pinch-zoom: ;
      --tw-scroll-snap-strictness: proximity;
      --tw-gradient-from-position: ;
      --tw-gradient-via-position: ;
      --tw-gradient-to-position: ;
      --tw-ordinal: ;
      --tw-slashed-zero: ;
      --tw-numeric-figure: ;
      --tw-numeric-spacing: ;
      --tw-numeric-fraction: ;
      --tw-ring-inset: ;
      --tw-ring-offset-width: 0px;
      --tw-ring-offset-color: #fff;
      --tw-ring-color: rgb(59 130 246 / 0.5);
      --tw-ring-offset-shadow: 0 0 #0000;
      --tw-ring-shadow: 0 0 #0000;
      --tw-shadow: 0 0 #0000;
      --tw-shadow-colored: 0 0 #0000;
      --tw-blur: ;
      --tw-brightness: ;
      --tw-contrast: ;
      --tw-grayscale: ;
      --tw-hue-rotate: ;
      --tw-invert: ;
      --tw-saturate: ;
      --tw-sepia: ;
      --tw-drop-shadow: ;
      --tw-backdrop-blur: ;
      --tw-backdrop-brightness: ;
      --tw-backdrop-contrast: ;
      --tw-backdrop-grayscale: ;
      --tw-backdrop-hue-rotate: ;
      --tw-backdrop-invert: ;
      --tw-backdrop-opacity: ;
      --tw-backdrop-saturate: ;
      --tw-backdrop-sepia: ;
      --tw-contain-size: ;
      --tw-contain-layout: ;
      --tw-contain-paint: ;
      --tw-contain-style:
    }

    ::backdrop {
      --tw-border-spacing-x: 0;
      --tw-border-spacing-y: 0;
      --tw-translate-x: 0;
      --tw-translate-y: 0;
      --tw-rotate: 0;
      --tw-skew-x: 0;
      --tw-skew-y: 0;
      --tw-scale-x: 1;
      --tw-scale-y: 1;
      --tw-pan-x: ;
      --tw-pan-y: ;
      --tw-pinch-zoom: ;
      --tw-scroll-snap-strictness: proximity;
      --tw-gradient-from-position: ;
      --tw-gradient-via-position: ;
      --tw-gradient-to-position: ;
      --tw-ordinal: ;
      --tw-slashed-zero: ;
      --tw-numeric-figure: ;
      --tw-numeric-spacing: ;
      --tw-numeric-fraction: ;
      --tw-ring-inset: ;
      --tw-ring-offset-width: 0px;
      --tw-ring-offset-color: #fff;
      --tw-ring-color: rgb(59 130 246 / 0.5);
      --tw-ring-offset-shadow: 0 0 #0000;
      --tw-ring-shadow: 0 0 #0000;
      --tw-shadow: 0 0 #0000;
      --tw-shadow-colored: 0 0 #0000;
      --tw-blur: ;
      --tw-brightness: ;
      --tw-contrast: ;
      --tw-grayscale: ;
      --tw-hue-rotate: ;
      --tw-invert: ;
      --tw-saturate: ;
      --tw-sepia: ;
      --tw-drop-shadow: ;
      --tw-backdrop-blur: ;
      --tw-backdrop-brightness: ;
      --tw-backdrop-contrast: ;
      --tw-backdrop-grayscale: ;
      --tw-backdrop-hue-rotate: ;
      --tw-backdrop-invert: ;
      --tw-backdrop-opacity: ;
      --tw-backdrop-saturate: ;
      --tw-backdrop-sepia: ;
      --tw-contain-size: ;
      --tw-contain-layout: ;
      --tw-contain-paint: ;
      --tw-contain-style:
    }

    /* ! tailwindcss v3.4.16 | MIT License | https://tailwindcss.com */
    *,
    ::after,
    ::before {
      box-sizing: border-box;
      border-width: 0;
      border-style: solid;
      border-color: #e5e7eb
    }

    ::after,
    ::before {
      --tw-content: ''
    }

    :host,
    html {
      line-height: 1.5;
      -webkit-text-size-adjust: 100%;
      -moz-tab-size: 4;
      tab-size: 4;
      font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
      font-feature-settings: normal;
      font-variation-settings: normal;
      -webkit-tap-highlight-color: transparent
    }

    body {
      margin: 0;
      line-height: inherit
    }

    hr {
      height: 0;
      color: inherit;
      border-top-width: 1px
    }

    abbr:where([title]) {
      -webkit-text-decoration: underline dotted;
      text-decoration: underline dotted
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      font-size: inherit;
      font-weight: inherit
    }

    a {
      color: inherit;
      text-decoration: inherit
    }

    b,
    strong {
      font-weight: bolder
    }

    code,
    kbd,
    pre,
    samp {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-feature-settings: normal;
      font-variation-settings: normal;
      font-size: 1em
    }

    small {
      font-size: 80%
    }

    sub,
    sup {
      font-size: 75%;
      line-height: 0;
      position: relative;
      vertical-align: baseline
    }

    sub {
      bottom: -.25em
    }

    sup {
      top: -.5em
    }

    table {
      text-indent: 0;
      border-color: inherit;
      border-collapse: collapse
    }

    button,
    input,
    optgroup,
    select,
    textarea {
      font-family: inherit;
      font-feature-settings: inherit;
      font-variation-settings: inherit;
      font-size: 100%;
      font-weight: inherit;
      line-height: inherit;
      letter-spacing: inherit;
      color: inherit;
      margin: 0;
      padding: 0
    }

    button,
    select {
      text-transform: none
    }

    button,
    input:where([type=button]),
    input:where([type=reset]),
    input:where([type=submit]) {
      -webkit-appearance: button;
      background-color: transparent;
      background-image: none
    }

    :-moz-focusring {
      outline: auto
    }

    :-moz-ui-invalid {
      box-shadow: none
    }

    progress {
      vertical-align: baseline
    }

    ::-webkit-inner-spin-button,
    ::-webkit-outer-spin-button {
      height: auto
    }

    [type=search] {
      -webkit-appearance: textfield;
      outline-offset: -2px
    }

    ::-webkit-search-decoration {
      -webkit-appearance: none
    }

    ::-webkit-file-upload-button {
      -webkit-appearance: button;
      font: inherit
    }

    summary {
      display: list-item
    }

    blockquote,
    dd,
    dl,
    figure,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    hr,
    p,
    pre {
      margin: 0
    }

    fieldset {
      margin: 0;
      padding: 0
    }

    legend {
      padding: 0
    }

    menu,
    ol,
    ul {
      list-style: none;
      margin: 0;
      padding: 0
    }

    dialog {
      padding: 0
    }

    textarea {
      resize: vertical
    }

    input::placeholder,
    textarea::placeholder {
      opacity: 1;
      color: #9ca3af
    }

    [role=button],
    button {
      cursor: pointer
    }

    :disabled {
      cursor: default
    }

    audio,
    canvas,
    embed,
    iframe,
    img,
    object,
    svg,
    video {
      display: block;
      vertical-align: middle
    }

    img,
    video {
      max-width: 100%;
      height: auto
    }

    [hidden]:where(:not([hidden=until-found])) {
      display: none
    }

    [type='text'],
    input:where(:not([type])),
    [type='email'],
    [type='url'],
    [type='password'],
    [type='number'],
    [type='date'],
    [type='datetime-local'],
    [type='month'],
    [type='search'],
    [type='tel'],
    [type='time'],
    [type='week'],
    [multiple],
    textarea,
    select {
      -webkit-appearance: none;
      appearance: none;
      background-color: #fff;
      border-color: #6b7280;
      border-width: 1px;
      border-radius: 0px;
      padding-top: 0.5rem;
      padding-right: 0.75rem;
      padding-bottom: 0.5rem;
      padding-left: 0.75rem;
      font-size: 1rem;
      line-height: 1.5rem;
      --tw-shadow: 0 0 #0000;
    }

    [type='text']:focus,
    input:where(:not([type])):focus,
    [type='email']:focus,
    [type='url']:focus,
    [type='password']:focus,
    [type='number']:focus,
    [type='date']:focus,
    [type='datetime-local']:focus,
    [type='month']:focus,
    [type='search']:focus,
    [type='tel']:focus,
    [type='time']:focus,
    [type='week']:focus,
    [multiple]:focus,
    textarea:focus,
    select:focus {
      outline: 2px solid transparent;
      outline-offset: 2px;
      --tw-ring-inset: var(--tw-empty,
          /*!*/
          /*!*/
        );
      --tw-ring-offset-width: 0px;
      --tw-ring-offset-color: #fff;
      --tw-ring-color: #2563eb;
      --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
      --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
      box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
      border-color: #2563eb
    }

    input::placeholder,
    textarea::placeholder {
      color: #6b7280;
      opacity: 1
    }

    ::-webkit-datetime-edit-fields-wrapper {
      padding: 0
    }

    ::-webkit-date-and-time-value {
      min-height: 1.5em;
      text-align: inherit
    }

    ::-webkit-datetime-edit {
      display: inline-flex
    }

    ::-webkit-datetime-edit,
    ::-webkit-datetime-edit-year-field,
    ::-webkit-datetime-edit-month-field,
    ::-webkit-datetime-edit-day-field,
    ::-webkit-datetime-edit-hour-field,
    ::-webkit-datetime-edit-minute-field,
    ::-webkit-datetime-edit-second-field,
    ::-webkit-datetime-edit-millisecond-field,
    ::-webkit-datetime-edit-meridiem-field {
      padding-top: 0;
      padding-bottom: 0
    }

    select {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.5rem center;
      background-repeat: no-repeat;
      background-size: 1.5em 1.5em;
      padding-right: 2.5rem;
      print-color-adjust: exact
    }

    [multiple],
    [size]:where(select:not([size="1"])) {
      background-image: initial;
      background-position: initial;
      background-repeat: unset;
      background-size: initial;
      padding-right: 0.75rem;
      print-color-adjust: unset
    }

    [type='checkbox'],
    [type='radio'] {
      -webkit-appearance: none;
      appearance: none;
      padding: 0;
      print-color-adjust: exact;
      display: inline-block;
      vertical-align: middle;
      background-origin: border-box;
      -webkit-user-select: none;
      user-select: none;
      flex-shrink: 0;
      height: 1rem;
      width: 1rem;
      color: #2563eb;
      background-color: #fff;
      border-color: #6b7280;
      border-width: 1px;
      --tw-shadow: 0 0 #0000
    }

    [type='checkbox'] {
      border-radius: 0px
    }

    [type='radio'] {
      border-radius: 100%
    }

    [type='checkbox']:focus,
    [type='radio']:focus {
      outline: 2px solid transparent;
      outline-offset: 2px;
      --tw-ring-inset: var(--tw-empty,
          /*!*/
          /*!*/
        );
      --tw-ring-offset-width: 2px;
      --tw-ring-offset-color: #fff;
      --tw-ring-color: #2563eb;
      --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
      --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
      box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow)
    }

    [type='checkbox']:checked,
    [type='radio']:checked {
      border-color: transparent;
      background-color: currentColor;
      background-size: 100% 100%;
      background-position: center;
      background-repeat: no-repeat
    }

    [type='checkbox']:checked {
      background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
    }

    @media (forced-colors: active) {
      [type='checkbox']:checked {
        -webkit-appearance: auto;
        appearance: auto
      }
    }

    [type='radio']:checked {
      background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
    }

    @media (forced-colors: active) {
      [type='radio']:checked {
        -webkit-appearance: auto;
        appearance: auto
      }
    }

    [type='checkbox']:checked:hover,
    [type='checkbox']:checked:focus,
    [type='radio']:checked:hover,
    [type='radio']:checked:focus {
      border-color: transparent;
      background-color: currentColor
    }

    [type='checkbox']:indeterminate {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
      border-color: transparent;
      background-color: currentColor;
      background-size: 100% 100%;
      background-position: center;
      background-repeat: no-repeat;
    }

    @media (forced-colors: active) {
      [type='checkbox']:indeterminate {
        -webkit-appearance: auto;
        appearance: auto
      }
    }

    [type='checkbox']:indeterminate:hover,
    [type='checkbox']:indeterminate:focus {
      border-color: transparent;
      background-color: currentColor
    }

    [type='file'] {
      background: unset;
      border-color: inherit;
      border-width: 0;
      border-radius: 0;
      padding: 0;
      font-size: unset;
      line-height: inherit
    }

    [type='file']:focus {
      outline: 1px solid ButtonText;
      outline: 1px auto -webkit-focus-ring-color
    }

    .fixed {
      position: fixed
    }

    .absolute {
      position: absolute
    }

    .relative {
      position: relative
    }

    .sticky {
      position: sticky
    }

    .inset-0 {
      inset: 0px
    }

    .bottom-0 {
      bottom: 0px
    }

    .left-0 {
      left: 0px
    }

    .right-0 {
      right: 0px
    }

    .top-0 {
      top: 0px
    }

    .z-10 {
      z-index: 10
    }

    .z-50 {
      z-index: 50
    }

    .mb-4 {
      margin-bottom: 1rem
    }

    .mt-2 {
      margin-top: 0.5rem
    }

    .mt-4 {
      margin-top: 1rem
    }

    .flex {
      display: flex
    }

    .grid {
      display: grid
    }

    .hidden {
      display: none
    }

    .aspect-\[3\/4\] {
      aspect-ratio: 3/4
    }

    .size-10 {
      width: 2.5rem;
      height: 2.5rem
    }

    .min-h-screen {
      min-height: 100vh
    }

    .w-full {
      width: 100%
    }

    .min-w-\[200px\] {
      min-width: 200px
    }

    .max-w-md {
      max-width: 28rem
    }

    .translate-y-4 {
      --tw-translate-y: 1rem;
      transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
    }

    .translate-y-full {
      --tw-translate-y: 100%;
      transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
    }

    .transform {
      transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
    }

    .grid-cols-\[repeat\(auto-fit\2c minmax\(158px\2c 1fr\)\)\] {
      grid-template-columns: repeat(auto-fit, minmax(158px, 1fr))
    }

    .flex-col {
      flex-direction: column
    }

    .items-center {
      align-items: center
    }

    .justify-center {
      justify-content: center
    }

    .justify-between {
      justify-content: space-between
    }

    .justify-around {
      justify-content: space-around
    }

    .gap-1 {
      gap: 0.25rem
    }

    .gap-3 {
      gap: 0.75rem
    }

    .gap-4 {
      gap: 1rem
    }

    .overflow-x-auto {
      overflow-x: auto
    }

    .rounded {
      border-radius: 0.25rem
    }

    .rounded-full {
      border-radius: 9999px
    }

    .rounded-lg {
      border-radius: 0.5rem
    }

    .border-t {
      border-top-width: 1px
    }

    .border-\[\#382929\] {
      --tw-border-opacity: 1;
      border-color: rgb(56 41 41 / var(--tw-border-opacity, 1))
    }

    .bg-\[\#181111\] {
      --tw-bg-opacity: 1;
      background-color: rgb(24 17 17 / var(--tw-bg-opacity, 1))
    }

    .bg-\[\#261c1c\] {
      --tw-bg-opacity: 1;
      background-color: rgb(38 28 28 / var(--tw-bg-opacity, 1))
    }

    .bg-\[\#382929\] {
      --tw-bg-opacity: 1;
      background-color: rgb(56 41 41 / var(--tw-bg-opacity, 1))
    }

    .bg-\[\#e50914\] {
      --tw-bg-opacity: 1;
      background-color: rgb(229 9 20 / var(--tw-bg-opacity, 1))
    }

    .bg-black {
      --tw-bg-opacity: 1;
      background-color: rgb(0 0 0 / var(--tw-bg-opacity, 1))
    }

    .bg-opacity-60 {
      --tw-bg-opacity: 0.6
    }

    .bg-opacity-80 {
      --tw-bg-opacity: 0.8
    }

    .bg-cover {
      background-size: cover
    }

    .bg-center {
      background-position: center
    }

    .bg-no-repeat {
      background-repeat: no-repeat
    }

    .p-2 {
      padding: 0.5rem
    }

    .p-4 {
      padding: 1rem
    }

    .p-6 {
      padding: 1.5rem
    }

    .px-4 {
      padding-left: 1rem;
      padding-right: 1rem
    }

    .py-2 {
      padding-top: 0.5rem;
      padding-bottom: 0.5rem
    }

    .py-5 {
      padding-top: 1.25rem;
      padding-bottom: 1.25rem
    }

    .text-2xl {
      font-size: 1.5rem;
      line-height: 2rem
    }

    .text-3xl {
      font-size: 1.875rem;
      line-height: 2.25rem
    }

    .text-base {
      font-size: 1rem;
      line-height: 1.5rem
    }

    .text-xl {
      font-size: 1.25rem;
      line-height: 1.75rem
    }

    .text-xs {
      font-size: 0.75rem;
      line-height: 1rem
    }

    .font-bold {
      font-weight: 700
    }

    .font-medium {
      font-weight: 500
    }

    .font-semibold {
      font-weight: 600
    }

    .tracking-tight {
      letter-spacing: -0.025em
    }

    .text-\[\#b89d9f\] {
      --tw-text-opacity: 1;
      color: rgb(184 157 159 / var(--tw-text-opacity, 1))
    }

    .text-gray-300 {
      --tw-text-opacity: 1;
      color: rgb(209 213 219 / var(--tw-text-opacity, 1))
    }

    .text-white {
      --tw-text-opacity: 1;
      color: rgb(255 255 255 / var(--tw-text-opacity, 1))
    }

    .placeholder-gray-400::placeholder {
      --tw-placeholder-opacity: 1;
      color: rgb(156 163 175 / var(--tw-placeholder-opacity, 1))
    }

    .opacity-0 {
      opacity: 0
    }

    .shadow-lg {
      --tw-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
      --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
      box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
    }

    .transition-colors {
      transition-property: color, background-color, border-color, fill, stroke, -webkit-text-decoration-color;
      transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
      transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, -webkit-text-decoration-color;
      transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
      transition-duration: 150ms
    }

    .\@container {
      container-type: inline-size
    }

    .hover\:bg-\[\#382929\]:hover {
      --tw-bg-opacity: 1;
      background-color: rgb(56 41 41 / var(--tw-bg-opacity, 1))
    }

    .hover\:bg-\[\#c10712\]:hover {
      --tw-bg-opacity: 1;
      background-color: rgb(193 7 18 / var(--tw-bg-opacity, 1))
    }

    .hover\:text-gray-300:hover {
      --tw-text-opacity: 1;
      color: rgb(209 213 219 / var(--tw-text-opacity, 1))
    }

    .focus\:outline-none:focus {
      outline: 2px solid transparent;
      outline-offset: 2px
    }

    .focus\:ring-2:focus {
      --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
      --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
      box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
    }

    .focus\:ring-\[\#e50914\]:focus {
      --tw-ring-opacity: 1;
      --tw-ring-color: rgb(229 9 20 / var(--tw-ring-opacity, 1))
    }

    @container (min-width: 480px) {
      .\@\[480px\]\:px-4 {
        padding-left: 1rem;
        padding-right: 1rem
      }

      .\@\[480px\]\:py-3 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem
      }
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
      <a href="./Movies_Search.php" class="inline-block">
        <button id="search-btn" class="p-2 hover:bg-[#382929] rounded-full transition-colors" aria-label="Search movies">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
            <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
          </svg>
        </button>
      </a>
    </header>

    <!-- Search Modal -->
    <!-- <div id="search-modal" class="fixed inset-0 bg-black bg-opacity-80 items-center justify-center z-50 hidden">
      <div class="bg-[#261c1c] p-6 rounded-lg w-full max-w-md modal transform opacity-0 translate-y-4">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Search Movies</h2>
          <button id="close-modal" class="text-white hover:text-gray-300" aria-label="Close search modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
              <path d="M165.66,101.66,139.31,128l26.35,26.34a8,8,0,0,1-11.32,11.32L128,139.31l-26.34,26.35a8,8,0,0,1-11.32-11.32L116.69,128,90.34,101.66a8,8,0,0,1,11.32-11.32L128,116.69l26.34-26.35a8,8,0,0,1,11.32,11.32Z"></path>
            </svg>
          </button>
        </div>
        <input type="text" id="search-input" class="w-full p-2 rounded bg-[#382929] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#e50914]" placeholder="Search for movies...">
        <div id="search-results" class="mt-4"></div>
      </div>
    </div> -->

    <!-- Hero Slider -->
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
          <div class="slide" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%), url('https://images.unsplash.com/photo-1616530940355-351fabd68c34?ixlib=rb-4.0.3&amp;auto=format&amp;fit=crop&amp;w=1920&amp;q=80');">
            <div class="p-6">
              <h2 class="text-3xl font-bold tracking-tight">Mystic Shadows</h2>
              <p class="text-gray-300 mt-2 max-w-md">A thrilling fantasy tale of ancient magic.</p>
              <button class="mt-4 bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
            </div>
          </div>
          <div class="slide" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%), url('https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&amp;auto=format&amp;fit=crop&amp;w=1920&amp;q=80');">
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
      <button class="genre-filter selected bg-[#e50914]" data-genre="all">All</button>
      <button class="genre-filter bg-[#382929]" data-genre="featured">Featured</button>
      <button class="genre-filter bg-[#382929]" data-genre="top10">Top 10</button>
      <button class="genre-filter bg-[#382929]" data-genre="trending">Trending</button>
      <button class="genre-filter bg-[#382929]" data-genre="crime">Crime/Thriller</button>
      <button class="genre-filter bg-[#382929]" data-genre="comedy">Comedy</button>
      <button class="genre-filter bg-[#382929]" data-genre="action">Action</button>
      <button class="genre-filter bg-[#382929]" data-genre="romance">Love Story</button>
      <button class="genre-filter bg-[#382929]" data-genre="biopic">Biopic</button>
      <button class="genre-filter bg-[#382929]" data-genre="southindian">South Indian</button>
      <button class="genre-filter bg-[#382929]" data-genre="bollywood">Bollywood</button>
      <button class="genre-filter bg-[#382929]" data-genre="hollywood">Hollywood</button>
      <button class="genre-filter bg-[#382929]" data-genre="bengali">Bengali</button>
      <div class="scroll-indicator scroll-indicator-left" aria-label="Scroll filters left">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
          <path d="M181.66,133.66l-48,48a8,8,0,0,1-11.32-11.32L156.69,136H48a8,8,0,0,1,0-16H156.69l-34.35-34.34a8,8,0,0,1,11.32-11.32l48,48A8,8,0,0,1,181.66,133.66Z"></path>
        </svg>
      </div>
      <div class="scroll-indicator scroll-indicator-right active" aria-label="Scroll filters right">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
          <path d="M74.34,133.66l48,48a8,8,0,0,1-11.32,11.32L76.69,158.66H168a8,8,0,0,1,0,16H76.69l34.35-34.34a8,8,0,0,1,11.32,11.32Z"></path>
        </svg>
      </div>
    </nav>

    <!-- Featured Section -->
    <div class="content-below-filters">
      <section class="genre-section active" data-genre="featured">
        <div class="flex justify-between items-center px-4 py-5">
          <h2 class="text-2xl font-bold tracking-tight">Featured</h2>
          <button class="genre-filter hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors bg-[#382929]" data-genre="featured">See More</button>
        </div>
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
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4 hidden" id="featured-all">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAN8dnftzBVq605UHKPM3r5zQyg5Xlv10R3uLe38-GCet4Ja42VIwfhbxsttR2-HwGewahDnUOu3jnYOzNLWA-hUYoWpzlcm0RDMvscZ9wdJ4wVXjxeqi3-pt0oJu8v6IERqFfHh9pjfvH77lknQneJBOFZ2Rcug0Rl7oxSCJfb9AzyFDEDByFr6BkoFvfUNwliW4ioNfa5U1qAR7bANxTCRUXMCHazeGl_7QTGBg1pwjCX8_N0wTDgOSVxw_deka-E2vBC2z339a7C');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">The Silent Echo</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDP1m_9zvRXYksciUtGz3aGM9EgWmjOHzmp6Ef_iusg9xrqFYJv_2MZS4LfaRRjXrKz346JzWnNzEfFWNVKaWmynu--7qU8A3xHY4UUCFoehHPp0s5kgs2uOWO-4e1M7-sDUALE2S5ZjHPyYZhKZR89gATUkcfyLojbnbza3rANHaVYY_mH5amy2yWJZGGuO4wacxGZLBdMHvBgtvlNu-RrfO7TNkUzjJ0tcO8sjQao6mr97k1x1jYYPmgx9mOho_90b-iguz9KLbaq');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Crimson Tide</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC6n1_i8Xkk8nj65ezS8HHn5Q7aooTzRaQVERDX5pYQpu3c1Kl_NLAJvDAVWkY73k4bRMNMA__3mE94z9UVzw8GTg2jg2vrAfS-a2xdgUlCmp1SRsqlLORpYsgwdjCh9F4QUr22101kev7XA16MpFN9nhWvDYEBP0BLAsdjEqfVkz9BmmHncqekq2qNSXLezr6gDfRdnuS0FoP_mTuptqYDU7HNoG23nq1a6MwxSu8HWDZSSuboJDWvWXPne_bPwDMJOY-yTgIVx8lc');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Whispers of the Past</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">The Last Frontier</p>
          </div>
        </div>
      </section>

      <!-- Genre Sections -->
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
      <section class="genre-section active" data-genre="top10">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Top 10</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Movie Title 1</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Movie Title 2</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Movie Title 3</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB_Ve2-uvwFF1k9uTcCAX9VbZWJ59bcnIQVHUHzTOkV3UERA-OLB8ekbS8JY1iy3W3Pu504W9NKBEDFTRgq-LRfaE88tAXObh39qHU0KAw0L-1LIfVyUe4LaTu_79ED3JMo8ITXBElXZXnaM_Zn3RO0QfQxxbuSuz-BF3gMtfAyFBeY4dwYxOQbTfyQSIYHkzyQxvR3V4ZKph1j8P68tFP9kdEZfhueCPjCHXdwfhT01d51sowbleu3ZRPDR51rZAowUklFkSx90Sa8');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Movie Title 4</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Movie Title 5</p>
          </div>
        </div>
      </section>
      <section class="genre-section active" data-genre="trending">
        <h2 class="text-2xl font-bold tracking-tight px-4 py-5">Trending</h2>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-4 p-4">
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCxf8-neeXlvXGYE0yj_1DG3WnwEzDFPS0M-fhHqTiwcF8Ji0eCx2NKorbzN5hf6rxotxR97U7kP6nWajyglmgyfY8a6rufrF6ks8AWf3vBGHc5GVR5Bb8qSSfHByASgva8ex6rEWs2zW4sihmFB-nq6Z7LB6HcZ12rhO0JHRgRtOsnC3C0wn2P-09234bfVhDsWcObAWFERBiLyeCouYPdSD0GpbliZSEH8THIGwf_qfertsITPYPmqO4rYOjD2HajIB-qs5HEN51B');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Trending Movie 1</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBBfsm7L8Z2pgLCmdUvC_TV6R_ibLBbarS_IGBl1Zi1FlSz5WxuWwuwFSlOVwYXO6Jdm3Eg_w8Q9AOUHrx3HKt1G2RiD1CpiXr34ODg1b53g_ZEI7jBUyYjrLQ8dgo5GDuF6SCvVT6NS6OjY3KJHE2ZZQ5P9wMM8G4Jtge4y68ELhvzhumauWfHEAms04AKTZO79JjMW27aZ4LmJTMGSWVv0r4Rcx4UeRlEuZUbqtSmoxCtF4ZMA22gT9hsnzsm0NZ1dQVCMROQRDe0');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Trending Movie 2</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDbv6IJI6y8QDJ6cujnTdc8xZvRbELXAMRTN-Nwo1UYVNmLAR0A97gcNuaYGDsrrwajRGy6GEWE96h3pABr4SCj-Vb6sHnxl3d-_H_KVlJT8XbIGNyVWfzZ3NQ6JrW8HinZDkyu9tAWv1GkrgGIZy4IiOfsUhGvUMsPoXx6rtNO4fR4H4N7YQ7s0N2JRSr7MBYXKFbqWAinDWbJPx2iqKWihWrxZYroaw53WNyzUuP80TXXR03V4eoHehBRZ-WqxdCp3yLVfwk_2eti');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Trending Movie 3</p>
          </div>
          <div class="movie-card flex flex-col gap-3 relative">
            <div class="relative w-full bg-center bg-no-repeat aspect-[3/4] bg-cover rounded-lg" style="background-image: url('https://lh3.googleuserc');">
              <div class="movie-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transform translate-y-full flex items-center justify-center rounded-lg">
                <button class="bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
              </div>
            </div>
            <p class="text-base font-medium">Trending Movie 4</p>
          </div>
        </div>
      </section>
      <!-- trending end -->

      <section class="genre-section active" data-genre="comedy">
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
      <section class="genre-section active" data-genre="action">
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
      <section class="genre-section active" data-genre="romance">
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
      <section class="genre-section active" data-genre="southindian">
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
      <section class="genre-section active" data-genre="bollywood">
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
      <section class="genre-section active" data-genre="hollywood">
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
      <section class="genre-section active" data-genre="bengali">
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
    const apiKey = 'abc123def456'; // Replace with your TMDB API key
    const baseImageUrl = 'https://image.tmdb.org/t/p/w1280';
    const trendingUrl = `https://api.themoviedb.org/3/trending/movie/week?api_key=${apiKey}`;

    async function fetchTrendingMovies() {
      try {
        const response = await fetch(trendingUrl);
        if (!response.ok) throw new Error('Failed to fetch trending movies');
        const data = await response.json();
        return data.results.slice(0, 5); // Limit to 5 movies for the slider
      } catch (error) {
        console.error('Error fetching movies:', error);
        return [];
      }
    }

    async function populateSlider() {
      const movies = await fetchTrendingMovies();
      const sliderContainer = document.querySelector('.slider-container');
      const navDotsContainer = document.querySelector('.nav-dots');

      // Clear existing content
      sliderContainer.querySelectorAll('.slide').forEach(slide => slide.remove());
      navDotsContainer.innerHTML = '';

      // Create slides and dots
      movies.forEach((movie, index) => {
        const slide = document.createElement('div');
        slide.classList.add('slide');
        if (index === 0) slide.classList.add('active');
        slide.style.backgroundImage = `linear-gradient(0deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%), url('${baseImageUrl}${movie.backdrop_path}')`;
        slide.innerHTML = `
          <div class="p-6">
            <h2 class="text-3xl font-bold tracking-tight">${movie.title}</h2>
            <p class="text-gray-300 mt-2 max-w-md">${movie.overview.substring(0, 100)}...</p>
            <button class="mt-4 bg-[#e50914] hover:bg-[#c10712] text-white font-semibold py-2 px-4 rounded transition-colors" onclick="window.location.href='Movies_WatchNow.php'">Watch Now</button>
          </div>
        `;
        sliderContainer.insertBefore(slide, navDotsContainer);

        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (index === 0) dot.classList.add('active');
        dot.dataset.slide = index;
        navDotsContainer.appendChild(dot);
      });

      // Slider navigation
      let currentSlide = 0;
      const slides = document.querySelectorAll('.slide');
      const dots = document.querySelectorAll('.dot');

      function showSlide(index) {
        slides.forEach((slide, i) => {
          slide.classList.toggle('active', i === index);
          dots[i].classList.toggle('active', i === index);
        });
        currentSlide = index;
      }

      dots.forEach(dot => {
        dot.addEventListener('click', () => showSlide(parseInt(dot.dataset.slide)));
      });

      document.querySelector('.prev').addEventListener('click', () => {
        showSlide((currentSlide - 1 + slides.length) % slides.length);
      });

      document.querySelector('.next').addEventListener('click', () => {
        showSlide((currentSlide + 1) % slides.length);
      });

      // Auto-slide every 5 seconds
      setInterval(() => {
        showSlide((currentSlide + 1) % slides.length);
      }, 5000);
    }

    // Initialize the slider
    populateSlider();
  </script>
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

    searchInput.addEventListener('input', e => {
      const query = e.target.value.toLowerCase();
      const mockResults = ['The Silent Echo', 'Crimson Tide', 'Whispers of the Past', 'The Last Frontier']
        .filter(title => title.toLowerCase().includes(query));
      searchResults.innerHTML = mockResults.length ?
        mockResults.map(title => `<p class="p-2 hover:bg-[#382929] cursor-pointer rounded">${title}</p>`).join('') :
        '<p class="text-gray-400">No results found</p>';
    });

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

    // Initialize with 'All' selected
    document.addEventListener('DOMContentLoaded', () => {
      const firstButton = document.querySelector('.genre-filter');
      if (firstButton) setSelectedGenre(firstButton);
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
  </script>

</body><chatgpt-sidebar data-gpts-theme="light"></chatgpt-sidebar>

</html>