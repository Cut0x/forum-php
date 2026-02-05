<?php
class Parsedown
{
    public function text(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        $html = '';
        $inList = false;

        foreach ($lines as $line) {
            $trim = trim($line);

            if ($trim === '') {
                if ($inList) {
                    $html .= "</ul>";
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.*)$/', $trim, $m)) {
                if ($inList) {
                    $html .= "</ul>";
                    $inList = false;
                }
                $level = strlen($m[1]);
                $html .= sprintf('<h%d>%s</h%d>', $level, $this->inline($m[2]), $level);
                continue;
            }

            if (preg_match('/^[\*-]\s+(.*)$/', $trim, $m)) {
                if (!$inList) {
                    $html .= "<ul>";
                    $inList = true;
                }
                $html .= '<li>' . $this->inline($m[1]) . '</li>';
                continue;
            }

            if ($inList) {
                $html .= "</ul>";
                $inList = false;
            }

            $html .= '<p>' . $this->inline($trim) . '</p>';
        }

        if ($inList) {
            $html .= "</ul>";
        }

        return $html;
    }

    private function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
        return $text;
    }
}
