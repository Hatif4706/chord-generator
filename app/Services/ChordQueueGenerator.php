<?php

namespace App\Services;

class ChordQueueGenerator
{
    private ChordTree $tree;

    // Master pola — identik dengan Python
    private array $masterPola = [
        'Pola 1' => ['Verse 1', 'Reff 1', 'Verse 2', 'Reff 2'],
        'Pola 2' => ['Verse 1', 'Reff',   'Verse 2'],
        'Pola 3' => ['Verse',   'Reff 1', 'Reff 2'],
    ];

    public function __construct(ChordTree $tree)
    {
        $this->tree = $tree;
    }

    public function generate(string $genre, string $family, string $pola): array
    {
        if (!isset($this->masterPola[$pola])) {
            return ['queue' => [], 'status' => 'error', 'message' => "Pola '{$pola}' tidak ditemukan!", 'meta' => []];
        }

        $familyNode = $this->tree->getFamilyNode($genre, $family);
        if ($familyNode === null) {
            return ['queue' => [], 'status' => 'error', 'message' => "Kombinasi '{$genre}' + '{$family}' tidak ditemukan di Tree!", 'meta' => []];
        }

        $versePool = $familyNode->getChild('Verse') ?? [];
        $reffPool  = $familyNode->getChild('Reff')  ?? [];

        // Buat paket chord dengan randomisasi — pengganti random.sample Python
        $paketChords = [
            'Verse'   => $this->shuffleCopy($versePool),
            'Verse 1' => $this->shuffleCopy($versePool),
            'Verse 2' => $this->shuffleCopy($versePool),
            'Reff'    => $this->shuffleCopy($reffPool),
            'Reff 1'  => $this->shuffleCopy($reffPool),
            'Reff 2'  => $this->shuffleCopy($reffPool),
        ];

        // === QUEUE — SplQueue = pengganti deque Python ===
        $songQueue = new \SplQueue();

        foreach ($this->masterPola[$pola] as $seksi) {
            // for r in range(2) → perulangan 2× berturut-turut
            for ($r = 0; $r < 2; $r++) {
                foreach ($paketChords[$seksi] as $chord) {
                    $songQueue->enqueue([               // .append() di Python
                        'seksi'      => "{$seksi} (Rep " . ($r + 1) . ")",
                        'akor'       => $chord,
                        'not'        => $this->tree->chordNotesMap[$chord] ?? [],
                        'seksi_base' => $seksi,
                        'repetisi'   => $r + 1,
                    ]);
                }
            }
        }

        // Dequeue semua item — .popleft() di Python
        $queueArray = [];
        $no = 1;
        while (!$songQueue->isEmpty()) {
            $item = $songQueue->dequeue();
            $item['nomor'] = $no++;
            $queueArray[] = $item;
        }

        $uniqueChords  = array_unique(array_column($queueArray, 'akor'));
        $sectionGroups = $this->groupBySection($queueArray);

        return [
            'queue'   => $queueArray,
            'status'  => 'success',
            'message' => 'Sukses',
            'meta'    => [
                'total_chords'   => count($queueArray),
                'unique_chords'  => array_values($uniqueChords),
                'sections'       => $this->masterPola[$pola],
                'section_groups' => $sectionGroups,
                'genre'          => $genre,
                'family'         => $family,
                'pola'           => $pola,
                'pola_desc'      => $this->tree->polaDescriptions[$pola]['desc'] ?? '',
            ],
        ];
    }

    // Pengganti random.sample(pool, len(pool)) di Python
    private function shuffleCopy(array $arr): array
    {
        $copy = $arr;
        shuffle($copy);
        return $copy;
    }

    private function groupBySection(array $queue): array
    {
        $groups = [];
        foreach ($queue as $item) {
            $key = $item['seksi'];
            if (!isset($groups[$key])) {
                $groups[$key] = ['label' => $key, 'base' => $item['seksi_base'], 'rep' => $item['repetisi'], 'chords' => []];
            }
            $groups[$key]['chords'][] = ['nomor' => $item['nomor'], 'akor' => $item['akor'], 'not' => $item['not']];
        }
        return array_values($groups);
    }
}
