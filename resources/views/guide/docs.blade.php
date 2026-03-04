@extends('guide.layout')

@section('title', 'Documentation — Magpie Components')

@section('content')
<style>
  /* Additional docs-specific styles */
  .docs-content {
    max-width: 800px;
  }
  
  .docs-content h1 {
    font-size: 42px;
    margin-bottom: 24px;
  }
  
  .docs-content h2 {
    scroll-margin-top: 24px;
  }
  
  .docs-content h2 a {
    color: inherit;
    text-decoration: none;
  }
  
  .docs-content h2 a:hover {
    text-decoration: underline;
  }
  
  .docs-content ul li ul,
  .docs-content ul li ol {
    margin-top: 8px;
    margin-bottom: 8px;
  }
  
  .docs-content ol {
    counter-reset: item;
  }
  
  .docs-content ol li {
    display: block;
  }
  
  .docs-content ol li::before {
    content: counters(item, ".") ". ";
    counter-increment: item;
    color: var(--text-muted);
    font-weight: 500;
  }
  
  /* Code language labels */
  pre {
    position: relative;
  }
  
  pre[class*="language-"]::before,
  pre code[class*="language-"]::before {
    content: attr(data-language);
    position: absolute;
    top: 0;
    right: 0;
    padding: 4px 12px;
    background: var(--bg-tertiary);
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 0 0 0 6px;
  }
  
  /* Highlight important notes */
  .docs-content blockquote strong:first-child {
    color: var(--accent-primary);
  }
  
  /* Parameter tables */
  .docs-content table code {
    font-size: 0.85em;
  }
  
  /* Back to top link */
  .back-to-top {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 48px;
    padding: 10px 16px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
  }
  
  .back-to-top:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    text-decoration: none;
  }
</style>

<div class="docs-content">
  <h1>Developer Documentation</h1>
  
  <p>Complete guide to integrating Magpie Components into your application. Learn how to securely collect payment card data using iframe-based elements.</p>
  
  <div style="display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap;">
    <a href="{{ route('guide.examples') }}" class="btn btn-primary">View Examples</a>
    <a href="/examples/01-basic/index.html" class="btn btn-secondary" target="_blank">Try Basic Demo</a>
  </div>
  
  {!! $content !!}
  
  <a href="#" class="back-to-top">↑ Back to top</a>
</div>
@endsection
