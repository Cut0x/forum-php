<?php

namespace App\Services\Markdown;

use App\Models\Emote;
use App\Models\User;
use DOMDocument;
use DOMDocumentFragment;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class MarkdownRenderer
{
    /**
     * Convertit du Markdown en HTML sûr, avec émotes ":nom:" et mentions "@pseudo" liées.
     */
    public function toHtml(string $markdown): string
    {
        $html = (string) $this->converter()->convert($markdown);
        $html = $this->replaceInTextNodes($html, fn (string $text) => $this->expandEmotes($text));
        $html = $this->replaceInTextNodes($html, fn (string $text) => $this->expandMentions($text));

        return $html;
    }

    protected function converter(): CommonMarkConverter
    {
        return new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 25,
        ]);
    }

    /**
     * @return array<string, array{file: string, title: string}>
     */
    protected function enabledEmotes(): array
    {
        return Cache::remember('emotes.enabled', now()->addHour(), function () {
            return Emote::query()
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (Emote $emote) => [$emote->name => ['file' => $emote->file, 'title' => (string) $emote->title]])
                ->all();
        });
    }

    /**
     * Remplace ":nom:" par une image d'émote dans un fragment de texte.
     * Retourne null si aucun remplacement n'est nécessaire.
     */
    protected function expandEmotes(string $text): ?DOMDocumentFragment
    {
        $emotes = $this->enabledEmotes();
        if (! $emotes || ! preg_match('/:([a-zA-Z0-9_+-]{2,50}):/', $text)) {
            return null;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $fragment = $doc->createDocumentFragment();
        $parts = preg_split('/:([a-zA-Z0-9_+-]{2,50}):/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                if ($part !== '') {
                    $fragment->appendChild($doc->createTextNode($part));
                }

                continue;
            }

            if (! isset($emotes[$part])) {
                $fragment->appendChild($doc->createTextNode(':'.$part.':'));

                continue;
            }

            $emote = $emotes[$part];
            $img = $doc->createElement('img');
            $img->setAttribute('src', asset('storage/emotes/'.$emote['file']));
            $img->setAttribute('alt', ':'.$part.':');
            $img->setAttribute('class', 'emote');
            if ($emote['title'] !== '') {
                $img->setAttribute('title', $emote['title']);
            }
            $fragment->appendChild($img);
        }

        return $fragment;
    }

    /**
     * Remplace "@pseudo" par un lien vers le profil, si l'utilisateur existe.
     */
    protected function expandMentions(string $text): ?DOMDocumentFragment
    {
        if (! preg_match('/@([a-zA-Z0-9_]{3,30})/', $text)) {
            return null;
        }

        preg_match_all('/@([a-zA-Z0-9_]{3,30})/', $text, $matches);
        $usernames = array_values(array_unique(array_map('strtolower', $matches[1])));

        $existing = Cache::remember(
            'mentions.'.md5(implode(',', $usernames)),
            now()->addMinutes(5),
            fn () => User::query()->whereIn('username', $usernames)->pluck('username')->all()
        );
        $existingLower = array_map('strtolower', $existing);

        if (! $existing) {
            return null;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $fragment = $doc->createDocumentFragment();
        $parts = preg_split('/@([a-zA-Z0-9_]{3,30})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                if ($part !== '') {
                    $fragment->appendChild($doc->createTextNode($part));
                }

                continue;
            }

            $lower = strtolower($part);
            $key = array_search($lower, $existingLower, true);
            if ($key === false) {
                $fragment->appendChild($doc->createTextNode('@'.$part));

                continue;
            }

            $link = $doc->createElement('a', '@'.$part);
            $link->setAttribute('href', route('profile.show', $existing[$key]));
            $link->setAttribute('class', 'mention');
            $fragment->appendChild($link);
        }

        return $fragment;
    }

    /**
     * Parcourt les nœuds texte du HTML (hors <code>/<pre>) et applique $callback
     * à chacun ; si le callback retourne un fragment, il remplace le nœud texte.
     */
    protected function replaceInTextNodes(string $html, callable $callback): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors($prev);

        $xpath = new DOMXPath($doc);
        $textNodes = $xpath->query('//text()');
        if (! $textNodes) {
            return $html;
        }

        foreach (iterator_to_array($textNodes) as $textNode) {
            if ($this->isInsideTag($textNode, ['code', 'pre', 'a'])) {
                continue;
            }

            $fragment = $callback($textNode->nodeValue);
            if ($fragment instanceof DOMDocumentFragment) {
                $imported = $doc->importNode($fragment, true);
                $textNode->parentNode?->replaceChild($imported, $textNode);
            }
        }

        $root = $doc->documentElement;
        if (! $root) {
            return $html;
        }

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $doc->saveHTML($child);
        }

        return $output;
    }

    protected function isInsideTag(DOMNode $node, array $tags): bool
    {
        $tags = array_map('strtolower', $tags);
        $current = $node;
        while ($current) {
            if ($current->nodeType === XML_ELEMENT_NODE && in_array(strtolower($current->nodeName), $tags, true)) {
                return true;
            }
            $current = $current->parentNode;
        }

        return false;
    }
}
