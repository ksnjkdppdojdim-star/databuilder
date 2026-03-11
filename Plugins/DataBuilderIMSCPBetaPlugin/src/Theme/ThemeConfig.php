<?php

namespace DataBuilder\Theme;

/**
 * Lit et expose la configuration d'un thème depuis son theme.xml
 */
class ThemeConfig
{
    private $data = [];
    private $loaded = false;
    private $themePath;

    public function __construct(string $themePath)
    {
        $this->themePath = $themePath;
    }

    public function load(): void
    {
        if ($this->loaded) return;

        $file = $this->themePath . '/theme.xml';

        if (!file_exists($file)) {
            throw new \RuntimeException("theme.xml not found in: [{$this->themePath}]");
        }

        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \RuntimeException("Failed to parse theme.xml in: [{$this->themePath}]");
        }

        $this->data = [
            'name'    => (string)($xml->name    ?? ''),
            'title'   => (string)($xml->title   ?? ''),
            'parent'  => (string)($xml->parent  ?? ''),
            'version' => (string)($xml->version ?? '1.0.0'),
            'author'  => (string)($xml->author  ?? ''),
        ];

        $this->loaded = true;
    }

    public function get(string $key, $default = null)
    {
        $this->load();
        return $this->data[$key] ?? $default;
    }

    public function getName(): string    { return $this->get('name', ''); }
    public function getTitle(): string   { return $this->get('title', ''); }
    public function getParent(): string  { return $this->get('parent', ''); }
    public function getVersion(): string { return $this->get('version', '1.0.0'); }
    public function getAuthor(): string  { return $this->get('author', ''); }
    public function hasParent(): bool    { return $this->getParent() !== ''; }

    public function all(): array
    {
        $this->load();
        return $this->data;
    }
}