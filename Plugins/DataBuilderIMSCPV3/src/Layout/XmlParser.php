<?php

namespace DataBuilder\Layout;

class XmlParser
{
    /**
     * Parse un fichier XML de layout et retourne un tableau normalisé
     */
    public function parseFile(string $filepath): array
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Layout file not found: {$filepath}");
        }

        $xml = simplexml_load_file($filepath, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \RuntimeException("Failed to parse XML layout: {$filepath}");
        }

        return $this->parseNode($xml);
    }

    /**
     * Parse un nœud XML récursivement
     */
    private function parseNode(\SimpleXMLElement $node): array
    {
        $result = [
            'tag'        => $node->getName(),
            'attributes' => $this->parseAttributes($node),
            'children'   => [],
        ];

        foreach ($node->children() as $child) {
            $result['children'][] = $this->parseNode($child);
        }

        return $result;
    }

    /**
     * Extrait les attributs d'un nœud XML en tableau PHP
     */
    private function parseAttributes(\SimpleXMLElement $node): array
    {
        $attributes = [];
        foreach ($node->attributes() as $key => $value) {
            $attributes[(string)$key] = (string)$value;
        }
        return $attributes;
    }

    /**
     * Parse directement depuis une string XML (utile pour les tests)
     */
    public function parseString(string $xmlString): array
    {
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new \RuntimeException("Failed to parse XML string.");
        }

        return $this->parseNode($xml);
    }
}