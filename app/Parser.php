<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Phpml\Classification\Classifier;
use Phpml\ModelManager;
use UnderflowException;
use UnexpectedValueException;

class Parser
{
    protected const ALTERNATE_SPELLINGS = [
        'checky' => 'chequy',
        'goute' => 'goutte',
    ];

    protected const MULTIWORDTOKENS = [
        // three-word tokens
        'a pair of',
        'barry bendy sinister',
        'chequy of nine',
        'fleur de lys',
        'goutte de poix',
        'gyronny of eight',
        'gyronny of twelve',
        'paly bendy sinister',
        'per bend sinister',
        'pile in point',
        'pily bendy sinister',
        'quarterly of nine',
        'tierced in pairle',
        'tierced in pall',
        'tierced per bend',
        'tierced per bend sinister',
        'tierced per fess',
        'tierced per pale',

        // two-word tokens
        'barry bendy',
        'bendy sinister',
        'fillet cross',
        'fillet pall',
        'fillet saltire',
        'maple leaf',
        'paly bendy',
        'per bend',
        'per chevron',
        'per fess',
        'per pale',
        'per saltire',
        'pily bendy',
    ];

    protected const PARTITIONS = [
        'per-pale',
        'per-fess',
        'per-bend',
        'per-chevron',
        'per-saltire',
        'quarterly',
        'gyronny-of-twelve',
        'barry',
        'barry-bendy',
        'bendy',
        'bendy-sinister',
        'paly',
        'paly-bendy',
        'chevronny',
        'chequy-of-nine',
        'chequy-of-sixteen',
        'lozengy',
        'pily-bendy',
        'pily-bendy-sinister',
    ];

    protected const TRIPARTITIONS = [
        'tierced-per-pale',
        'tierced-per-fess',
        'tierced-per-bend',
    ];

    protected const TINCTURES = [
        'or', 'argent', 'gold', 'silver',                                           // metals
        'gules', 'azure', 'vert', 'purpure', 'sable', 'bleu-celeste',               // colors
        'murrey', 'sanguine', 'orange', 'tenne', 'brown',                           // stains
        'ermine', 'ermines', 'erminois', 'pean',                                    // furs
        'gross-vair', 'vair', 'countervair', 'vairy',
        'potent', 'counterpotent',
        'proper',
    ];

    protected const ORDINARIES = [
        'bar',
        'barrulet',
        'bend',
        'bend-sinister',
        'bendlet',
        'bendlet-sinister',
        'chevron',
        'chevronel',
        'chief',
        'cross',
        'fess',
        'fillet-cross',
        'fillet-pall',
        'fillet-saltire',
        'pale',
        'pall',
        'pallet',
        'pile-in-point',
        'riband',
        'saltire',
    ];

    protected const SUBORDINARIES = [];

    protected const SEMYS = [
        'billety' => 'billet',
        'billetty' => 'billet',
        'bezanty' => 'bezant',
        'crescenty' => 'crescent',
        'crusily' => 'cross',
        'delphy' => 'delf',
        'escallopy' => 'escallop',
        'estencely' => 'spark',
        'estoilly' => 'estoile',
        'fleury' => 'fleur-de-lys',
        'semy-de-lys' => 'fleur-de-lys',
        'goutty' => 'goute',
        'goutte' => 'goute',
        'mullety' => 'mullet',
        'platy' => 'plate',
    ];

    protected const CHARGES_UNMODIFIABLE = [
        'fleur-de-lys',
        'hammer',
        'maple-leaf',
    ];

    protected const CHARGES_MODIFIABLE = [
        'cross' => ['bottony', 'crosslet', 'fleuretty', 'fleury', 'formy', 'maltese', 'moline', 'patonce', 'patty', 'pommee', 'potent'],
    ];

    protected const BEASTS = [
        'eagle' => ['displayed'],
        'lion' => ['combatant', 'cowed', 'guardant', 'passant', 'rampant', 'regardant', 'reguardant', 'salient', 'sejant', 'statant'],
    ];

    protected const BEAST_MODIFIERS = [
        'langued',
        'clawed',
    ];

    protected const NUMBERS = [
        'a' => 1,
        'an' => 1,
        'one' => 1,
        'two' => 2,
        'a-pair-of' => 2,
        'three' => 3,
        'four' => 4,
        'five' => 5,
        'six' => 6,
        'seven' => 7,
        'eight' => 8,
        'nine' => 9,
        'ten' => 10,
        'eleven' => 11,
        'twelve' => 12,
    ];

    protected const SPECIAL_PLURALS = [
        'fleur-de-lys' => 'fleur-de-lys',
    ];

    /** @var string */
    protected $input;

    /** @var Collection */
    protected $sanitizedInput;

    /** @var Classifier */
    protected $classifier;

    /** @var ModelManager */
    protected $manager;

    public function __construct($input)
    {
        $this->input = $input;

        $this->sanitizedInput = $this->sanitize($input);

        // $this->classifier = app(Classifier::class);

        // $this->manager = new ModelManager();
    }

    /**
     * @param  string $input
     * @return array  $output
     */
    public function sanitize($input)
    {
        return Str::of($input)
            ->ascii()                                           // convert accented characters to ascii equivalents
            ->lower()                                           // make lowercase
            ->trim()                                            // remove leading/trailing whitespace
            ->replaceMatches('/[^A-Za-z\d\s\-]+/', '')          // keep only A-Z, a-z, digits, whitespace, and hyphens
            ->replaceMatches('/\s+/', ' ')                      // remove extra whitespace
            ->replace(                                          // slugify multi-word tokens so they parse as single tokens
                self::MULTIWORDTOKENS,
                array_map('Str::slug', self::MULTIWORDTOKENS)
            )
            ->explode(' ');                                     // string to collection
    }

    public function parse($component = null)
    {
        // dump($this->input, $this->sanitizedInput);

        $phrase = null;
        $number = null;
        $parsingField = true;
        $parsingOrdinary = false;
        $parsingCharge = false;
        $mod = null;
        $lastTokenClass = null;
        $lastNumber = null;

        $arms = [];

        while (count($this->sanitizedInput) > 0) {

            $phrase = $token = $this->getNextToken();

            if (in_array($token, ['and', 'on'])) {
                continue;
            }

            if (in_array($token, ['bend']) && $this->peekNextToken() === 'sinister') {
                $token .= ' ' . $this->getNextToken();
            }

            $phrase .= " $token";

            $number = $this->parseNumber($token);
            $semy = $this->parseSemy($token);
            $partition = $parsingField ? $this->parsePartition($token) : false;
            $tincture = $this->parseTincture($token);
            $charge = $this->parseCharge($token);
            $beast = $this->parseBeast($token);

            $unknown = !($number || $semy || $partition || $tincture || $charge);
            if ($unknown) {
                throw new UnexpectedValueException("Unknown token: $token");
            }

            if ($number !== false) {
                $parsingField = false;
                $lastNumber = $number;
            }

            if (!$number && $semy) {
            }

            if ($partition) {
                if (!data_get($arms, 'field.partition')) {
                    $arms['field']['partition'][] = $partition;
                }

                continue;
            }


            if ($parsingField && $tincture) {
                $fieldTinctures = data_get($arms, 'field.tincture');

                if (data_get($arms, 'field.partition')) {
                    $arms['field']['fields'][] = ['tincture' => [$tincture]];
                } else {
                    $arms['field']['tincture'][] = $tincture;
                }
            }

            if ($charge) {
                $parsingField = false;
                $parsingOrdinary = false;
                $parsingCharge = true;
                $parsedCharge = ['charge' => $charge['charge'], 'number' => $lastNumber];

                // look for charge modifiers
                while (in_array($this->peekNextToken(), $charge['modifiers'])) {
                    $parsedCharge['modifiers'][] = $this->getNextToken();
                }

                // set the charge's main color
                if ($tincture = $this->parseTincture($this->peekNextToken())) {
                    $parsedCharge['tincture'][] = $this->getNextToken();
                }

                // check for beast-specific modifiers e.g. langued, clawed
                if ($beast) {
                    while (in_array($this->peekNextToken(), self::BEAST_MODIFIERS, true)) {
                        $modifier = $this->getNextToken();

                        if ($this->parseTincture($this->peekNextToken())) {
                            $parsedCharge['tincture'][$modifier] = $this->getNextToken();
                        }
                    }
                }

                $arms['field']['charges'][] = $parsedCharge;
            }
        }

        return $arms;
    }

    /**
     * @param  array  $blazon
     * @param  array  $output
     */
    public function train($blazon, $output): void
    {
        $this->classifier->train($this->sanitize($blazon), $output);

        $this->manager->saveToFile($this->classifier, env('ML_FILE_PATH'));
    }

    /**
     * @param  string $blazon
     * @return array
     */
    public function predict($blazon)
    {
        return $this->classifier->predict([$this->sanitize($blazon)]);
    }

    private function getNextToken() {
        $token = $this->sanitizedInput->shift();

        if (!$token) {
            throw new UnderflowException('Unexpected end of blazon.');
        }

        return $token;
    }

    private function peekNextToken()
    {
        return data_get($this->sanitizedInput, 0);
    }

    private function getSingular($token)
    {
        if (in_array($token, self::SPECIAL_PLURALS)) {
            return self::SPECIAL_PLURALS[$token];
        } else {
            return Str::of($token)->singular()->__toString();
        }
    }

    private function parseCharge($token)
    {
        $token = $this->getSingular($token);

        if (in_array($token, self::CHARGES_UNMODIFIABLE)) {
            return ['charge' => $token, 'modifiers' => []];
        }

        if (array_key_exists($token, self::CHARGES_MODIFIABLE)) {
            return ['charge' => $token, 'modifiers' => self::CHARGES_MODIFIABLE[$token]];
        }

        if (array_key_exists($token, self::BEASTS)) {
            return ['charge' => $token, 'modifiers' => self::BEASTS[$token]];
        }

        return false;
    }

    private function parseBeast($token)
    {
        $token = $this->getSingular($token);

        return array_key_exists($token, self::BEASTS);
    }

    private function parseNumber($token)
    {
        return self::NUMBERS[$token] ?? false;
    }

    private function parsePartition($token)
    {
        if (in_array($token, array_merge(self::PARTITIONS, self::TRIPARTITIONS))) {
            return $token;
        }

        return false;
    }

    private function parseSemy($token)
    {
        return data_get(self::SEMYS, $token, false);
    }

    private function parseTincture($token)
    {
        return in_array($token, self::TINCTURES) ? $token : false;
    }
}