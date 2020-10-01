# Armorer

An engine for parsing heraldry blazons.

## Blazon structure

Data is parsed into a standard blazon data format, described below with examples using PHP array syntax.

The base element in all blazons is the `field`, which can have the following sub-elements: either a `tincture` *or* a `partition` (e.g. per fess) + `fields` (individual partitions), and optionally `charges` or `ordinaries`.  Ordinaries may have their own charges.  Charges generally have a `charge`, `number`, and `tincture`, and optionally a `position`.  Most elements can also  have `modifiers` (e.g. complex lines of partition, a charge's posture or position, tincture patterns, etc).

Due to the potential for complex arrangements, tinctures, partitions, charges, and modifiers are generally encoded as arrays, with array order important.

### Examples

*Azure, a lion passant argent.*
```
[
    'field' => [
        'tincture' => ['azure'],
        'charges' => [
            [
                'charge' => 'lion',
                'number' => 1,
                'modifiers' => ['passant'],
                'tincture' => ['argent'],
            ],
        ],
    ],
],
```

*Per fess wavy argent and azure, in the first a hammer proper.*
```
[
    'field' => [
        'partition' => [
            'per-fess',
            'modifiers' => ['wavy'],
        ],
        'fields' => [
            [
                'tincture' => ['argent'],
                'charges' => [
                    [
                        'charge' => 'hammer',
                        'number' => 1,
                        'tincture' => ['proper'],
                    ],
                ],
            ],
            [
                'tincture' => ['azure'],
            ],
        ],
    ],
],
```

*Fretty argent and sable, an eagle displayed gules langued azure.*
```
[
    'field' => [
        'tincture' => [
            'argent',
            'sable',
            'modifiers' => ['fretty'],
        ],
        'charges' => [
            [
                'charge' => 'eagle',
                'number' => 1,
                'modifiers' => ['displayed'],
                'tincture' => [
                    'sable',
                    'langued' => 'azure'
                ],
            ],
        ],
    ],
],
```

*Vert, a bend sinister invected sable between two mullets Or.*
> Note: due to the ordinary's position *between* relative to the charges recorded in the blazon, the charges are separated and given absolute positions *dexter-chief* and *sinister-base* respectively.
```
[
    'field' => [
        'tincture' => ['vert'],
        'ordinaries' => [
            [
                'ordinary' => 'bend-sinister',
                'modifiers' => ['invected'],
                'tincture' => ['sable],
            ],
        ]
        'charges' => [
            [
                'charge' => 'mullet',
                'number' => 1,
                'position' => 'dexter-chief'
                'tincture' => ['or'],
            ],
            [
                'charge' => 'mullet',
                'number' => 1,
                'position' => 'sinister-base'
                'tincture' => ['or'],
            ],
        ],
    ],
],
```