<?php

namespace App\Services;

class ChordTree
{
    public TreeNode $root;

    public array $chordNotesMap = [
        'C'      => ['C4', 'E4', 'G4'],
        'G'      => ['G3', 'B3', 'D4'],
        'Am'     => ['A3', 'C4', 'E4'],
        'F'      => ['F3', 'A3', 'C4'],
        'Dm'     => ['D3', 'F3', 'A3'],
        'Em'     => ['E3', 'G3', 'B3'],
        'Dm7'    => ['D3', 'F3', 'A3', 'C4'],
        'G7'     => ['G3', 'B3', 'D4', 'F4'],
        'Cmaj7'  => ['C4', 'E4', 'G4', 'B4'],
        'A7'     => ['A3', 'C#4', 'E4', 'G4'],
        'G_Maj'  => ['G3', 'B3', 'D4'],
        'C_Maj'  => ['C4', 'E4', 'G4'],
        'D_Maj'  => ['D3', 'F#3', 'A3'],
        'Em_Cl'  => ['E3', 'G3', 'B3'],
    ];

    public array $instrumentPrograms = [
        'Piano'     => ['program' => 0,  'label' => 'Acoustic Grand Piano',    'icon' => '🎹'],
        'Guitar'    => ['program' => 24, 'label' => 'Acoustic Guitar (Nylon)', 'icon' => '🎸'],
        'Bass'      => ['program' => 32, 'label' => 'Acoustic Bass',           'icon' => '🎵'],
        'Strings'   => ['program' => 48, 'label' => 'String Ensemble',         'icon' => '🎻'],
        'Synth Pad' => ['program' => 89, 'label' => 'Pad (Warm)',              'icon' => '🎛️'],
    ];

    public array $genreFamilyMap = [
        'Pop'     => ['C-Major Family'],
        'Jazz'    => ['D-Minor Family'],
        'Classic' => ['G-Major Family'],
    ];

    public array $polaDescriptions = [
        'Pola 1' => ['sections' => ['Verse 1', 'Reff 1', 'Verse 2', 'Reff 2'], 'desc' => 'Struktur lagu penuh dengan dua verse dan dua reff'],
        'Pola 2' => ['sections' => ['Verse 1', 'Reff', 'Verse 2'],             'desc' => 'Struktur sederhana tanpa reff kedua'],
        'Pola 3' => ['sections' => ['Verse', 'Reff 1', 'Reff 2'],              'desc' => 'Struktur dengan penekanan pada bagian reff'],
    ];

    public function __construct()
    {
        $this->buildTree();
    }

    private function buildTree(): void
    {
        $this->root = new TreeNode("Sistem Generator Chord");

        $popNode     = new TreeNode("Pop");
        $jazzNode    = new TreeNode("Jazz");
        $classicNode = new TreeNode("Classic");

        $this->root->addChild("Pop", $popNode);
        $this->root->addChild("Jazz", $jazzNode);
        $this->root->addChild("Classic", $classicNode);

        $cMajorFamily = new TreeNode("C-Major Family");
        $dMinorFamily = new TreeNode("D-Minor Family");
        $gMajorFamily = new TreeNode("G-Major Family");

        $popNode->addChild("C-Major Family", $cMajorFamily);
        $jazzNode->addChild("D-Minor Family", $dMinorFamily);
        $classicNode->addChild("G-Major Family", $gMajorFamily);

        $cMajorFamily->addChild("Verse", ["C", "G", "Am", "F"]);
        $cMajorFamily->addChild("Reff",  ["F", "G", "C", "Am", "Dm", "Em"]);

        $dMinorFamily->addChild("Verse", ["Dm7", "G7", "Cmaj7"]);
        $dMinorFamily->addChild("Reff",  ["Dm7", "G7", "A7", "Cmaj7"]);

        $gMajorFamily->addChild("Verse", ["G_Maj", "C_Maj", "G_Maj", "D_Maj"]);
        $gMajorFamily->addChild("Reff",  ["Em_Cl", "C_Maj", "D_Maj", "G_Maj"]);
    }

    public function getFamilyNode(string $genre, string $family): ?TreeNode
    {
        $genreNode = $this->root->getChild($genre);
        if (!$genreNode instanceof TreeNode) return null;

        $familyNode = $genreNode->getChild($family);
        if (!$familyNode instanceof TreeNode) return null;

        return $familyNode;
    }

    public function renderTree(): array
    {
        return $this->nodeToArray($this->root);
    }

    private function nodeToArray(mixed $node, int $depth = 0): array
    {
        if (is_array($node)) {
            return ['type' => 'leaf', 'value' => $node, 'depth' => $depth];
        }
        $result = ['type' => 'node', 'name' => $node->name, 'depth' => $depth, 'children' => []];
        foreach ($node->children as $key => $child) {
            $result['children'][$key] = $this->nodeToArray($child, $depth + 1);
        }
        return $result;
    }
}
