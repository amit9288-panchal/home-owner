<?php

namespace App\Services;

class HomeownerParser
{
    public function parse(string $input): array
    {
        $input = trim($input);
        $results = [];

        // Handle "Mr and Mrs Smith" style
        if (preg_match(
            '/^(Mr|Mister|Mrs|Ms|Dr|Prof)\s*(?:and|&)\s*(Mr|Mister|Mrs|Ms|Dr|Prof)\s+(.+)$/i',
            $input,
            $matches
        )) {
            $results[] = $this->build($matches[1], null, null, $matches[3]);
            $results[] = $this->build($matches[2], null, null, $matches[3]);
            return $results;
        }

        // Split "Mr John Smith and Ms Jane Doe"
        $parts = preg_split('/\s+(?:and|&)\s+/i', $input);
        foreach ($parts as $part) {
            $results[] = $this->parseSingle(trim($part));
        }

        return array_filter($results);
    }

    private function parseSingle(string $name): array
    {
        $tokens = preg_split('/\s+/', $name);
        $title = null;
        $first = null;
        $initial = null;
        $last = null;

        if ($tokens && in_array($tokens[0], ['Mr', 'Mister', 'Mrs', 'Ms', 'Dr', 'Prof'])) {
            $title = $tokens[0] === 'Mister' ? 'Mr' : $tokens[0];
            array_shift($tokens);
        }

        if ($tokens) {
            if (preg_match('/^[A-Z]\.?$/', $tokens[0])) {
                $initial = rtrim(array_shift($tokens), '.');
            } else {
                $first = array_shift($tokens);
            }
        }

        if ($tokens) {
            $last = implode(' ', $tokens);
        }

        return $this->build($title, $first, $initial, $last);
    }

    private function build(?string $title, ?string $first, ?string $initial, ?string $last): array
    {
        return [
            'title' => $title,
            'first_name' => $first,
            'initial' => $initial,
            'last_name' => $last,
        ];
    }
}
