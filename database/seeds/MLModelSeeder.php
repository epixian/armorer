<?php

use App\Parser;
use Illuminate\Database\Seeder;

class MLModelSeeder extends Seeder
{
    protected $trainingData = [
        'Gules, a lion rampant guardant Or.' => [
            'field' => ['partition' => [], 'tinctures' => ['gules']],
            'charges' => [
                ['charge' => 'lion', 'number' => 1, 'posture' => ['rampant', 'guardant'], 'tincture' => ['or']],
            ],
        ],
        'Azure, a lion passant argent.' => [
            'field' => ['partition' => [], 'tinctures' => ['azure']],
            'charges' => [
                ['charge' => 'lion', 'number' => 1, 'posture' => ['passant'], 'tincture' => ['argent']],
            ],
        ],
        'Argent, an eagle displayed sable langued azure.' => [
            'field' => ['partition' => [], 'tinctures' => ['gules']],
            'charges' => [
                ['charge' => 'eagle', 'number' => 1, 'posture' => ['displayed'], 'tincture' => ['sable', 'langued' => 'azure']],
            ],
        ],
        'Vert, an eagle displayed Or.' => [
            'field' => ['partition' => [], 'tinctures' => ['vert']],
            'charges' => [
                ['charge' => 'eagle', 'number' => 1, 'posture' => ['displayed'], 'tincture' => ['or']],
            ],
        ],
        'Per fess gules and or, an eagle displayed counterchanged.' => [
            'field' => ['partition' => ['per fess'], 'tinctures' => ['gules', 'or']],
            'charges' => [
                ['charge' => 'eagle', 'number' => 1, 'posture' => ['displayed'], 'tincture' => ['counterchanged']],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parser = new Parser();

        $parser->train(
            array_keys($this->trainingData),
            array_values($this->trainingData)
        );
    }
}
