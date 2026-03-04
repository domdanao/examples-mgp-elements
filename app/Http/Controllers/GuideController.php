<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;
use Parsedown;

final class GuideController extends Controller
{
    private const EXAMPLES = [
        [
            'id' => '01-basic',
            'title' => 'Basic',
            'description' => 'Minimal integration with no styling. The bare essentials to get up and running.',
            'path' => '/examples/01-basic/index.html',
        ],
        [
            'id' => '02-stripe',
            'title' => 'Stripe Style',
            'description' => 'Clean, professional design inspired by Stripe Checkout. Subtle shadows and focus rings.',
            'path' => '/examples/02-stripe/index.html',
        ],
        [
            'id' => '03-kitschy',
            'title' => 'Kitschy',
            'description' => 'Loud, colorful, and animated. Gradient backgrounds, Comic Sans, and bouncing emoji.',
            'path' => '/examples/03-kitschy/index.html',
        ],
        [
            'id' => '04-brutalist',
            'title' => 'Brutalist',
            'description' => 'Raw and stark. Monospace font, heavy black borders, zero decoration.',
            'path' => '/examples/04-brutalist/index.html',
        ],
        [
            'id' => '05-full-form',
            'title' => 'Full Form',
            'description' => 'Every available field: card address, owner billing & shipping, redirect URLs, and metadata.',
            'path' => '/examples/05-full-form/index.html',
        ],
    ];

    private const SECTIONS = [
        ['id' => 'overview', 'title' => 'Overview', 'anchor' => '1-overview'],
        ['id' => 'how-it-works', 'title' => 'How It Works', 'anchor' => '2-how-it-works'],
        ['id' => 'prerequisites', 'title' => 'Prerequisites', 'anchor' => '3-prerequisites'],
        ['id' => 'quick-start', 'title' => 'Quick Start', 'anchor' => '4-quick-start'],
        ['id' => 'sdk-reference', 'title' => 'SDK Reference', 'anchor' => '5-sdk-reference'],
        ['id' => 'element-types', 'title' => 'Element Types', 'anchor' => '6-element-types'],
        ['id' => 'styling', 'title' => 'Styling', 'anchor' => '7-styling'],
        ['id' => 'create-source', 'title' => 'createSource()', 'anchor' => '8-createsource'],
        ['id' => 'event-handling', 'title' => 'Event Handling', 'anchor' => '9-event-handling'],
        ['id' => 'postmessage', 'title' => 'postMessage Protocol', 'anchor' => '10-postmessage-protocol'],
        ['id' => 'api-reference', 'title' => 'API Reference', 'anchor' => '11-api-reference'],
        ['id' => 'allowlists', 'title' => 'Origin Allowlists', 'anchor' => '12-origin-allowlists'],
        ['id' => 'local-dev', 'title' => 'Local Development', 'anchor' => '13-local-development'],
        ['id' => 'production', 'title' => 'Production Setup', 'anchor' => '14-production-setup'],
        ['id' => 'security', 'title' => 'Security Model', 'anchor' => '15-security-model'],
        ['id' => 'troubleshooting', 'title' => 'Troubleshooting', 'anchor' => '16-error-handling--troubleshooting'],
        ['id' => 'full-example', 'title' => 'Full Working Example', 'anchor' => '17-full-working-example'],
    ];

    public function index(): View
    {
        return view('guide.index', [
            'sections' => self::SECTIONS,
            'examples' => self::EXAMPLES,
        ]);
    }

    public function docs(): View
    {
        $markdownPath = base_path('DEVELOPER.md');
        $markdown = file_get_contents($markdownPath);

        // Convert markdown to HTML
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false);
        $html = $parsedown->text($markdown);

        // Post-process HTML for better styling
        $html = $this->enhanceHtml($html);

        return view('guide.docs', [
            'content' => $html,
            'sections' => self::SECTIONS,
        ]);
    }

    public function examples(): View
    {
        return view('guide.examples', [
            'examples' => self::EXAMPLES,
            'sections' => self::SECTIONS,
        ]);
    }

    private function enhanceHtml(string $html): string
    {
        // Add anchor IDs to h2 headings based on their content
        $html = preg_replace_callback(
            '/<h2>(.*?)<\/h2>/',
            function ($matches) {
                $text = strip_tags($matches[1]);
                $id = $this->slugify($text);

                return '<h2 id="' . $id . '">' . $matches[1] . '</h2>';
            },
            $html
        );

        // Add anchor IDs to h3 headings
        $html = preg_replace_callback(
            '/<h3>(.*?)<\/h3>/',
            function ($matches) {
                $text = strip_tags($matches[1]);
                $id = $this->slugify($text);

                return '<h3 id="' . $id . '">' . $matches[1] . '</h3>';
            },
            $html
        );

        // Wrap tables in a container for scrolling
        $html = preg_replace(
            '/<table>(.*?)<\/table>/s',
            '<div class="table-wrapper"><table>$1</table></div>',
            $html
        );

        return $html;
    }

    private function slugify(string $text): string
    {
        // Convert to lowercase
        $text = strtolower($text);
        // Remove special characters
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        // Replace spaces and multiple hyphens with single hyphen
        $text = preg_replace('/[\s-]+/', '-', $text);
        // Trim hyphens from ends
        $text = trim($text, '-');

        return $text;
    }
}
