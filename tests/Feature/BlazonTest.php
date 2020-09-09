<?php

namespace Tests\Feature;

use App\Parser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use MLModelSeeder;
use Tests\TestCase;

class BlazonTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // $this->seed(MLModelSeeder::class);
    }
    /**
     * Data provider for parsing blazons.
     * @return array[][]
     */
    public function providesBlazons(): array
    {
        return [
            'azure, a lion passant argent' => [
                'Azure, a lion passant argent.',
                [
                    'field' => ['tinctures' => ['azure']],
                    'charges' => [
                        ['charge' => 'lion', 'number' => 1, 'modifiers' => ['passant'], 'tincture' => ['argent']],
                    ],
                ],
            ],
            'gules, a lion rampant or' => [
                'Gules, a lion rampant guardant Or.',
                [
                    'field' => ['tinctures' => ['gules']],
                    'charges' => [
                        ['charge' => 'lion', 'number' => 1, 'modifiers' => ['rampant', 'guardant'], 'tincture' => ['or']],
                    ],
                ],
            ],
            'argent, an eagle displayed sable langued azure' => [
                'Argent, an eagle displayed sable langued azure.',
                [
                    'field' => ['tinctures' => ['argent']],
                    'charges' => [
                        ['charge' => 'eagle', 'number' => 1, 'modifiers' => ['displayed'], 'tincture' => ['sable', 'langued' => 'azure']],
                    ],
                ],
            ],
            'vert, three fleur-de-lys argent' => [
                'Vert, three fleur-de-lys argent.',
                [
                    'field' => ['tinctures' => ['vert']],
                    'charges' => [
                        ['charge' => 'fleur-de-lys', 'number' => 3, 'tincture' => ['argent']],
                    ],
                ],
            ],
            'per fess argent and azure, a hammer proper' => [
                'Per fess argent and azure, two hammers proper.',
                [
                    'field' => ['partition' => 'per-fess', 'tinctures' => ['argent', 'azure']],
                    'charges' => [
                        ['charge' => 'hammer', 'number' => 2, 'tincture' => ['proper']],
                    ],
                ],
            ],
            'tierced per pale gules, argent, and gules, a maple leaf gules' => [
                'Tierced per pale gules, argent, and gules, a maple leaf gules.',
                [
                    'field' => ['partition' => 'tierced-per-pale', 'tinctures' => ['gules', 'argent', 'gules']],
                    'charges' => [
                        ['charge' => 'maple-leaf', 'number' => 1, 'tincture' => ['gules']],
                    ],
                ],
            ],

        ];
    }

    public function it_throws_an_exception_for_unrecognized_words(): void
    {
        $this->expectException('UnexpectedValueException');

        (new Parser('Argentina, a bear.'))->parse();
    }

    /**
     * @param  string $blazon
     * @param  array  $expectedOutput
     *
     * @test
     * @dataProvider providesBlazons
     */
    public function it_can_parse_a_field(string $blazon, array $expectedOutput): void
    {
        $this->parseAndCompare($blazon, $expectedOutput, 'field');
    }

    /**
     * @param  string $blazon
     * @param  array  $expectedOutput
     *
     * @test
     * @dataProvider providesBlazons
     */
    public function it_can_parse_charges(string $blazon, array $expectedOutput): void
    {
        $this->parseAndCompare($blazon, $expectedOutput, 'charges');
    }

    private function parseAndCompare(string $blazon, array $expectedOutput, string $key): void
    {
        $this->assertSame(
            data_get($expectedOutput, $key),
            data_get((new Parser($blazon))->parse(), $key)
        );
    }
}
