@extends('guide.layout')

@section('title', 'Magpie Components — Developer Guide')

@section('content')
<div class="hero">
  <div class="hero-icon">🪶</div>
  <h1>Magpie Components</h1>
  <p>A secure, iframe-based card input system for collecting payment card data without exposing sensitive information to your application. Clone of Stripe Elements.</p>
  <div class="hero-buttons">
    <a href="{{ route('guide.docs') }}" class="btn btn-primary">Read Documentation</a>
    <a href="{{ route('guide.examples') }}" class="btn btn-secondary">View Examples</a>
  </div>
</div>

<h2>Quick Start</h2>

<pre><code class="language-html">&lt;!-- 1. Add the SDK --&gt;
&lt;script src="https://components.magpie.im/sdk/magpie.js"&gt;&lt;/script&gt;

&lt;!-- 2. Create container elements --&gt;
&lt;div id="card-number"&gt;&lt;/div&gt;
&lt;div id="card-expiry"&gt;&lt;/div&gt;
&lt;div id="card-cvc"&gt;&lt;/div&gt;

&lt;!-- 3. Initialize and mount --&gt;
&lt;script&gt;
  const magpie = new Magpie("pk_live_your_key");
  const elements = magpie.elements();

  elements.create("cardNumber").mount("#card-number");
  elements.create("cardExpiry").mount("#card-expiry");
  elements.create("cardCvc").mount("#card-cvc");
&lt;/script&gt;</code></pre>

<div class="features">
  <div class="feature">
    <div class="feature-icon">🔒</div>
    <h3>Secure by Design</h3>
    <p>Card data is entered in cross-origin iframes. Your JavaScript never touches sensitive information.</p>
  </div>
  <div class="feature">
    <div class="feature-icon">⚡</div>
    <h3>Easy Integration</h3>
    <p>Drop-in JavaScript SDK. Just three lines to mount the card fields and one call to create a source.</p>
  </div>
  <div class="feature">
    <div class="feature-icon">🎨</div>
    <h3>Fully Customizable</h3>
    <p>Style the container elements however you want. Pass style options to control the iframe input appearance.</p>
  </div>
  <div class="feature">
    <div class="feature-icon">🌍</div>
    <h3>PCI Compliant</h3>
    <p>Reduce your PCI compliance scope. Card data never touches your servers or JavaScript.</p>
  </div>
</div>

<h2>Architecture Overview</h2>

<p>Magpie Components consists of three parts working together:</p>

<ul>
  <li><strong>SDK</strong> (<code>magpie.js</code>) — JavaScript library that creates and manages iframes</li>
  <li><strong>Components</strong> — HTML pages served from <code>https://components.magpie.im</code> that run inside iframes</li>
  <li><strong>Proxy API</strong> — Laravel backend that forwards card data securely to the Magpie API</li>
</ul>

<pre><code>Your Page
  └─ Loads magpie.js
       └─ Creates three iframes (cardNumber, cardExpiry, cardCvc)
            └─ Each iframe loads from https://components.magpie.im
                 └─ User types card data inside iframes
                      └─ On createSource():
                           └─ iframe POSTs card data to /api/v2/sources
                                └─ Proxy forwards to api.magpie.im
                                     └─ Returns source token (src_xxx)</code></pre>

<h2>Explore Examples</h2>

<p>See different integration styles and designs. Each example is a complete, working implementation you can copy and modify.</p>

<div class="examples-grid">
  @foreach($examples as $example)
  <a href="{{ $example['path'] }}" class="example-card" target="_blank">
    <div class="example-preview">
      <iframe src="{{ $example['path'] }}" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
    </div>
    <div class="example-info">
      <div class="example-title">{{ $example['title'] }}</div>
      <div class="example-desc">{{ $example['description'] }}</div>
      <div class="example-link">
        Open example →
      </div>
    </div>
  </a>
  @endforeach
</div>

<h2>Next Steps</h2>

<ul>
  <li>Read the full <a href="{{ route('guide.docs') }}">documentation</a> to understand all features and options</li>
  <li>Browse the <a href="{{ route('guide.examples') }}">examples</a> to see different integration styles</li>
  <li>Check the <a href="{{ route('guide.docs') }}#17-full-working-example">complete working example</a> for a copy-paste-ready integration</li>
</ul>
@endsection
