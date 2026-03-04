@extends('guide.layout')

@section('title', 'Examples — Magpie Components')

@section('content')
<style>
  .examples-page h1 {
    margin-bottom: 12px;
  }
  
  .examples-intro {
    color: var(--text-muted);
    font-size: 16px;
    margin-bottom: 32px;
    max-width: 600px;
  }
  
  .examples-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 28px;
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
    transform: translateY(-3px);
    border-color: var(--accent-primary);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.2);
    text-decoration: none;
  }
  
  .example-preview {
    height: 240px;
    background: linear-gradient(135deg, #F3F4F6 0%, #E5E7EB 100%);
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid var(--border-color);
  }
  
  .example-preview iframe {
    width: 800px;
    height: 560px;
    border: none;
    transform: scale(0.5);
    transform-origin: top left;
    pointer-events: none;
  }
  
  .example-preview::after {
    content: '';
    position: absolute;
    inset: 0;
  }
  
  .example-info {
    padding: 24px;
  }
  
  .example-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
  }
  
  .example-number {
    width: 28px;
    height: 28px;
    background: var(--bg-tertiary);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent-primary);
  }
  
  .example-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
  }
  
  .example-desc {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 16px;
  }
  
  .example-actions {
    display: flex;
    gap: 10px;
  }
  
  .example-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
  }
  
  .example-btn:hover {
    text-decoration: none;
  }
  
  .example-btn-primary {
    background: var(--accent-primary);
    color: white;
  }
  
  .example-btn-primary:hover {
    background: var(--accent-hover);
  }
  
  .example-btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-secondary);
  }
  
  .example-btn-secondary:hover {
    background: var(--border-color);
    color: var(--text-primary);
  }
  
  /* Direct links section */
  .quick-links {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-top: 32px;
  }
  
  .quick-links h3 {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
  }
  
  .quick-links-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .quick-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
  }
  
  .quick-link:hover {
    border-color: var(--accent-primary);
    color: var(--text-primary);
    text-decoration: none;
  }
  
  @media (max-width: 640px) {
    .examples-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="examples-page">
  <h1>Examples</h1>
  <p class="examples-intro">Live previews of different integration styles. Each example demonstrates unique styling approaches while using the same underlying SDK.</p>
  
  <div class="examples-grid">
    @foreach($examples as $index => $example)
    <div class="example-card">
      <div class="example-preview">
        <iframe src="{{ $example['path'] }}" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
      </div>
      <div class="example-info">
        <div class="example-header">
          <div class="example-number">{{ sprintf('%02d', $index + 1) }}</div>
          <div class="example-title">{{ $example['title'] }}</div>
        </div>
        <div class="example-desc">{{ $example['description'] }}</div>
        <div class="example-actions">
          <a href="{{ $example['path'] }}" class="example-btn example-btn-primary" target="_blank">
            Open Live Demo →
          </a>
          <a href="{{ $example['path'] }}" class="example-btn example-btn-secondary" target="_blank" onclick="viewSource(event, '{{ $example['path'] }}')">
            View Source
          </a>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  
  <div class="quick-links">
    <h3>Quick Access</h3>
    <div class="quick-links-grid">
      @foreach($examples as $example)
      <a href="{{ $example['path'] }}" class="quick-link" target="_blank">{{ $example['title'] }}</a>
      @endforeach
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function viewSource(event, path) {
    event.preventDefault();
    event.stopPropagation();

    // Fetch and display source code
    fetch(path)
      .then(r => r.text())
      .then(html => {
        const newWindow = window.open('', '_blank');
        newWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Source Code</title>
            <style>
              body {
                font-family: 'JetBrains Mono', monospace;
                background: #0F172A;
                color: #CBD5E1;
                padding: 20px;
                margin: 0;
              }
              pre {
                white-space: pre-wrap;
                word-break: break-all;
                font-size: 13px;
                line-height: 1.6;
              }
            </style>
          </head>
          <body>
            <pre id="source"></pre>
          </body>
          </html>
        `);
        const pre = newWindow.document.getElementById('source');
        pre.textContent = html;
        newWindow.document.close();
      });
  }
</script>
@endsection
