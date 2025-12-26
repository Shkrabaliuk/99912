<?php
/**
 * Parsedown - Markdown Parser
 * Спрощена версія для CMS
 * Оригінал: https://github.com/erusev/parsedown
 */

class Parsedown {
    private $safeMode = false;
    
    public function text($text) {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = trim($text);
        
        $lines = explode("\n", $text);
        $html = $this->lines($lines);
        
        return trim($html);
    }
    
    public function setSafeMode($safeMode) {
        $this->safeMode = (bool) $safeMode;
        return $this;
    }
    
    private function lines($lines) {
        $html = '';
        $inCodeBlock = false;
        $codeBlockContent = [];
        $codeBlockLang = '';
        $inList = false;
        $listItems = [];
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            // Code block
            if (preg_match('/^```(\w*)$/', $line, $matches)) {
                if (!$inCodeBlock) {
                    $inCodeBlock = true;
                    $codeBlockLang = $matches[1] ?? '';
                    $codeBlockContent = [];
                } else {
                    $inCodeBlock = false;
                    $code = implode("\n", $codeBlockContent);
                    $html .= '<pre><code' . ($codeBlockLang ? ' class="language-' . htmlspecialchars($codeBlockLang) . '"' : '') . '>' . htmlspecialchars($code) . '</code></pre>';
                    $codeBlockContent = [];
                    $codeBlockLang = '';
                }
                continue;
            }
            
            if ($inCodeBlock) {
                $codeBlockContent[] = $line;
                continue;
            }
            
            // Списки
            if (preg_match('/^[-*+]\s+(.+)$/', $line, $matches)) {
                $listItems[] = $matches[1];
                $inList = true;
                continue;
            } elseif (preg_match('/^(\d+)\.\s+(.+)$/', $line, $matches)) {
                $listItems[] = $matches[2];
                $inList = 'ordered';
                continue;
            } elseif ($inList && !empty($listItems)) {
                $tag = $inList === 'ordered' ? 'ol' : 'ul';
                $html .= "<{$tag}>";
                foreach ($listItems as $item) {
                    $html .= '<li>' . $this->inlineElements($item) . '</li>';
                }
                $html .= "</{$tag}>";
                $inList = false;
                $listItems = [];
            }
            
            // Заголовки
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $text = $matches[2];
                $html .= "<h{$level}>" . $this->inlineElements($text) . "</h{$level}>";
                continue;
            }
            
            // Blockquote
            if (preg_match('/^>\s+(.+)$/', $line, $matches)) {
                $html .= '<blockquote><p>' . $this->inlineElements($matches[1]) . '</p></blockquote>';
                continue;
            }
            
            // Horizontal rule
            if (preg_match('/^([-*_])\s*\1\s*\1+$/', $line)) {
                $html .= '<hr>';
                continue;
            }
            
            // Порожні рядки
            if (trim($line) === '') {
                continue;
            }
            
            // Звичайний параграф
            $html .= '<p>' . $this->inlineElements($line) . '</p>';
        }
        
        // Закриваємо список якщо залишився відкритим
        if ($inList && !empty($listItems)) {
            $tag = $inList === 'ordered' ? 'ol' : 'ul';
            $html .= "<{$tag}>";
            foreach ($listItems as $item) {
                $html .= '<li>' . $this->inlineElements($item) . '</li>';
            }
            $html .= "</{$tag}>";
        }
        
        return $html;
    }
    
    private function inlineElements($text) {
        // Links: [text](url)
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^\)]+)\)/', function($matches) {
            $linkText = $this->escape($matches[1]);
            $url = $this->escape($matches[2]);
            return '<a href="' . $url . '">' . $linkText . '</a>';
        }, $text);
        
        // Images: ![alt](url)
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^\)]+)\)/', function($matches) {
            $alt = $this->escape($matches[1]);
            $url = $this->escape($matches[2]);
            return '<img src="' . $url . '" alt="' . $alt . '">';
        }, $text);
        
        // Bold: **text** or __text__
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
        
        // Italic: *text* or _text_
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);
        
        // Inline code: `code`
        $text = preg_replace_callback('/`([^`]+)`/', function($matches) {
            return '<code>' . htmlspecialchars($matches[1]) . '</code>';
        }, $text);
        
        // Strikethrough: ~~text~~
        $text = preg_replace('/~~(.+?)~~/', '<del>$1</del>', $text);
        
        return $text;
    }
    
    private function escape($text) {
        if ($this->safeMode) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
        return $text;
    }
}