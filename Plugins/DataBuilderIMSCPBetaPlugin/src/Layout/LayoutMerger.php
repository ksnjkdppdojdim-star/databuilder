<?php

namespace DataBuilder\Layout;

/**
 * Fusionne plusieurs arbres de layout parsés en un seul arbre final.
 * Règles de merge :
 *  - Un container/block existant (même "name") est mergé, pas dupliqué.
 *  - L'attribut "before" ou "after" est respecté à la fin du merge.
 *  - Un nœud avec remove="true" supprime le nœud existant.
 */
class LayoutMerger
{
    public function merge(array $base, array $override): array
    {
        // Même tag racine attendu
        if ($base['tag'] !== $override['tag']) {
            return $override;
        }

        // Merge des attributs (override écrase base)
        $merged = [
            'tag'        => $base['tag'],
            'attributes' => array_merge($base['attributes'], $override['attributes']),
            'children'   => $base['children'],
        ];

        foreach ($override['children'] as $overrideChild) {
            $merged['children'] = $this->mergeChild($merged['children'], $overrideChild);
        }

        return $merged;
    }

    /**
     * Merge un enfant dans la liste des enfants existants.
     */
    private function mergeChild(array $existingChildren, array $newChild): array
    {
        $name = $newChild['attributes']['name'] ?? null;

        // Nœud sans "name" → toujours ajouté (pas de déduplication possible)
        if ($name === null) {
            $existingChildren[] = $newChild;
            return $existingChildren;
        }

        // Cherche si un enfant avec le même "name" existe déjà
        foreach ($existingChildren as $index => $child) {
            if (($child['attributes']['name'] ?? null) === $name) {

                // remove="true" → suppression du nœud
                if (($newChild['attributes']['remove'] ?? 'false') === 'true') {
                    array_splice($existingChildren, $index, 1);
                    return $existingChildren;
                }

                // Merge récursif
                $existingChildren[$index] = $this->merge($child, $newChild);
                return $existingChildren;
            }
        }

        // Nouveau nœud → insertion en tenant compte de before/after
        $existingChildren = $this->insertWithOrder($existingChildren, $newChild);
        return $existingChildren;
    }

    /**
     * Insère un nœud en respectant before/after.
     * before="-" → début, after="-" → fin (comportement Magento)
     */
    private function insertWithOrder(array $children, array $newChild): array
    {
        $before = $newChild['attributes']['before'] ?? null;
        $after  = $newChild['attributes']['after']  ?? null;

        if ($before === '-') {
            array_unshift($children, $newChild);
            return $children;
        }

        if ($after === '-' || ($before === null && $after === null)) {
            $children[] = $newChild;
            return $children;
        }

        // Insertion avant un nœud nommé
        if ($before !== null) {
            foreach ($children as $index => $child) {
                if (($child['attributes']['name'] ?? null) === $before) {
                    array_splice($children, $index, 0, [$newChild]);
                    return $children;
                }
            }
        }

        // Insertion après un nœud nommé
        if ($after !== null) {
            foreach ($children as $index => $child) {
                if (($child['attributes']['name'] ?? null) === $after) {
                    array_splice($children, $index + 1, 0, [$newChild]);
                    return $children;
                }
            }
        }

        // Fallback → fin
        $children[] = $newChild;
        return $children;
    }

    /**
     * Fusionne une liste de layouts dans l'ordre (chaque item override le précédent)
     */
    public function mergeAll(array $layouts): array
    {
        if (empty($layouts)) {
            throw new \RuntimeException('No layouts to merge.');
        }

        $result = array_shift($layouts);

        foreach ($layouts as $layout) {
            $result = $this->merge($result, $layout);
        }

        return $result;
    }
}