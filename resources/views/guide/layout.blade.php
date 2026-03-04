<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Magpie Components — Developer Guide')</title>
  <link rel="icon" type="image/png" href="/magpie-logo.png">
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    
    :root {
      --bg-primary: #0F172A;
      --bg-secondary: #1E293B;
      --bg-tertiary: #334155;
      --text-primary: #F8FAFC;
      --text-secondary: #CBD5E1;
      --text-muted: #94A3B8;
      --accent-primary: #6366F1;
      --accent-hover: #4F46E5;
      --accent-secondary: #8B5CF6;
      --border-color: #334155;
      --code-bg: #0F172A;
      --success: #10B981;
      --warning: #F59E0B;
      --error: #EF4444;
      --sidebar-width: 280px;
    }
    
    html {
      scroll-behavior: smooth;
      scroll-padding-top: 24px;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--bg-primary);
      color: var(--text-primary);
      line-height: 1.6;
      min-height: 100vh;
    }
    
    /* Layout */
    .app {
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--bg-secondary);
      border-right: 1px solid var(--border-color);
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      overflow-y: auto;
      z-index: 100;
      padding: 24px 0;
    }
    
    .sidebar-header {
      padding: 0 20px 24px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 20px;
    }
    
    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-primary);
    }
    
    .logo-icon {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .logo-icon img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .logo-text {
      font-size: 16px;
      font-weight: 700;
      letter-spacing: -0.3px;
    }
    
    .logo-text span {
      color: var(--accent-primary);
    }
    
    .sidebar-section {
      padding: 0 16px;
      margin-bottom: 24px;
    }
    
    .sidebar-title {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
      padding: 0 8px;
      margin-bottom: 8px;
    }
    
    .nav-list {
      list-style: none;
    }
    
    .nav-item {
      margin-bottom: 2px;
    }
    
    .nav-link {
      display: block;
      padding: 7px 12px;
      border-radius: 6px;
      color: var(--text-secondary);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.15s;
    }
    
    .nav-link:hover {
      background: var(--bg-tertiary);
      color: var(--text-primary);
    }
    
    .nav-link.active {
      background: var(--accent-primary);
      color: white;
    }
    
    /* Main content */
    .main {
      flex: 1;
      margin-left: var(--sidebar-width);
      padding: 40px 48px;
      max-width: calc(var(--sidebar-width) + 900px);
    }
    
    /* Typography */
    h1 {
      font-size: 36px;
      font-weight: 700;
      letter-spacing: -0.5px;
      margin-bottom: 16px;
      line-height: 1.2;
    }
    
    h2 {
      font-size: 24px;
      font-weight: 600;
      margin-top: 48px;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border-color);
      line-height: 1.3;
    }
    
    h3 {
      font-size: 18px;
      font-weight: 600;
      margin-top: 32px;
      margin-bottom: 12px;
      color: var(--text-primary);
    }
    
    h4 {
      font-size: 15px;
      font-weight: 600;
      margin-top: 24px;
      margin-bottom: 10px;
      color: var(--text-secondary);
    }
    
    p {
      margin-bottom: 16px;
      color: var(--text-secondary);
    }
    
    a {
      color: var(--accent-primary);
      text-decoration: none;
    }
    
    a:hover {
      text-decoration: underline;
    }
    
    /* Lists */
    ul, ol {
      margin-bottom: 16px;
      padding-left: 24px;
    }
    
    li {
      margin-bottom: 8px;
      color: var(--text-secondary);
    }
    
    li::marker {
      color: var(--text-muted);
    }
    
    /* Code */
    code {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.9em;
      background: var(--bg-tertiary);
      padding: 2px 6px;
      border-radius: 4px;
      color: var(--text-primary);
    }
    
    pre {
      background: var(--code-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 20px;
      overflow-x: auto;
      margin-bottom: 20px;
    }
    
    pre code {
      background: none;
      padding: 0;
      font-size: 13px;
      line-height: 1.6;
      color: var(--text-secondary);
    }
    
    /* Tables */
    .table-wrapper {
      overflow-x: auto;
      margin-bottom: 20px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    
    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    
    th {
      background: var(--bg-secondary);
      font-weight: 600;
      color: var(--text-primary);
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover td {
      background: var(--bg-secondary);
    }
    
    /* Blockquotes */
    blockquote {
      border-left: 4px solid var(--accent-primary);
      background: var(--bg-secondary);
      padding: 16px 20px;
      margin-bottom: 20px;
      border-radius: 0 8px 8px 0;
    }
    
    blockquote p {
      margin-bottom: 0;
    }
    
    blockquote p:last-child {
      margin-bottom: 0;
    }
    
    /* Horizontal rule */
    hr {
      border: none;
      border-top: 1px solid var(--border-color);
      margin: 40px 0;
    }
    
    /* Strong text */
    strong {
      color: var(--text-primary);
      font-weight: 600;
    }
    
    /* Navigation pills for examples */
    .nav-pills {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 32px;
    }
    
    .nav-pill {
      padding: 8px 16px;
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      color: var(--text-secondary);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.15s;
    }
    
    .nav-pill:hover {
      background: var(--bg-tertiary);
      color: var(--text-primary);
      text-decoration: none;
    }
    
    .nav-pill.active {
      background: var(--accent-primary);
      border-color: var(--accent-primary);
      color: white;
    }
    
    /* Examples grid */
    .examples-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
      margin-top: 32px;
    }
    
    .example-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.2s;
      text-decoration: none;
      display: block;
    }
    
    .example-card:hover {
      transform: translateY(-2px);
      border-color: var(--accent-primary);
      box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15);
      text-decoration: none;
    }
    
    .example-preview {
      height: 200px;
      background: #F3F4F6;
      position: relative;
      overflow: hidden;
    }
    
    .example-preview iframe {
      width: 680px;
      height: 480px;
      border: none;
      transform: scale(0.45);
      transform-origin: top left;
      pointer-events: none;
    }
    
    .example-preview::after {
      content: '';
      position: absolute;
      inset: 0;
    }
    
    .example-info {
      padding: 20px;
    }
    
    .example-title {
      font-size: 15px;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 6px;
    }
    
    .example-desc {
      font-size: 13px;
      color: var(--text-muted);
      line-height: 1.5;
    }
    
    .example-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 14px;
      font-size: 13px;
      font-weight: 500;
      color: var(--accent-primary);
    }
    
    /* Hero section */
    .hero {
      text-align: center;
      padding: 60px 20px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 48px;
    }
    
    .hero-icon {
      width: 64px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
    }
    
    .hero-icon img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .hero h1 {
      font-size: 42px;
      margin-bottom: 12px;
    }
    
    .hero p {
      font-size: 18px;
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto 32px;
    }
    
    .hero-buttons {
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.15s;
      border: none;
      cursor: pointer;
    }
    
    .btn-primary {
      background: var(--accent-primary);
      color: white;
    }
    
    .btn-primary:hover {
      background: var(--accent-hover);
      text-decoration: none;
    }
    
    .btn-secondary {
      background: var(--bg-tertiary);
      color: var(--text-primary);
    }
    
    .btn-secondary:hover {
      background: var(--border-color);
      text-decoration: none;
    }
    
    /* Feature cards */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin: 40px 0;
    }
    
    .feature {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 24px;
    }
    
    .feature-icon {
      width: 44px;
      height: 44px;
      background: var(--bg-tertiary);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 16px;
    }
    
    .feature h3 {
      margin-top: 0;
      font-size: 15px;
    }
    
    .feature p {
      font-size: 13px;
      margin-bottom: 0;
    }
    
    /* Responsive */
    @media (max-width: 900px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s;
      }
      
      .sidebar.open {
        transform: translateX(0);
      }
      
      .main {
        margin-left: 0;
        padding: 24px;
      }
      
      .mobile-toggle {
        display: block;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 101;
        width: 44px;
        height: 44px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-primary);
        cursor: pointer;
      }
    }
    
    @media (min-width: 901px) {
      .mobile-toggle {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
    
    <aside class="sidebar">
      <div class="sidebar-header">
        <a href="{{ route('guide.index') }}" class="logo">
          <div class="logo-icon"><img src="/magpie-logo.png" alt="Magpie"></div>
          <div class="logo-text">Magpie <span>Components</span></div>
        </a>
      </div>
      
      <div class="sidebar-section">
        <div class="sidebar-title">Guide</div>
        <ul class="nav-list">
          <li class="nav-item">
            <a href="{{ route('guide.index') }}" class="nav-link {{ request()->routeIs('guide.index') ? 'active' : '' }}">Home</a>
          </li>
          <li class="nav-item">
            <a href="{{ route('guide.docs') }}" class="nav-link {{ request()->routeIs('guide.docs') ? 'active' : '' }}">Documentation</a>
          </li>
          <li class="nav-item">
            <a href="{{ route('guide.examples') }}" class="nav-link {{ request()->routeIs('guide.examples') ? 'active' : '' }}">Examples</a>
          </li>
        </ul>
      </div>
      
      <div class="sidebar-section">
        <div class="sidebar-title">Documentation</div>
        <ul class="nav-list">
          @foreach($sections as $section)
          <li class="nav-item">
            <a href="{{ route('guide.docs') }}#{{ $section['anchor'] }}" class="nav-link">{{ $section['title'] }}</a>
          </li>
          @endforeach
        </ul>
      </div>
    </aside>
    
    <main class="main">
      @yield('content')
    </main>
  </div>
  
  @yield('scripts')
</body>
</html>
