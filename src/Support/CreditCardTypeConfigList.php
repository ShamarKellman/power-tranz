<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

class CreditCardTypeConfigList
{
    /**
     * @return array<string, array{
     *     niceType: string,
     *     type: string,
     *     patterns: array<int|array<int, int>>,
     *     gaps: array<int>,
     *     lengths: array<int|array<int, int>>,
     *     code: array{
     *     name: string,
     *     size: int
     *   },
     *   luhnCheck: bool
     * }>
     */
    public static function get(): array
    {
        return [
            CreditCardValidator::TYPE_VISA => [
                'niceType' => 'Visa',
                'type' => CreditCardValidator::TYPE_VISA,
                'patterns' => [
                    4,
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                    18,
                    19,
                ],
                'code' => [
                    'name' => 'CVV',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_MASTERCARD => [
                'niceType' => 'Mastercard',
                'type' => CreditCardValidator::TYPE_MASTERCARD,
                'patterns' => [
                    [51, 55],
                    [2221, 2229],
                    [223, 229],
                    [23, 26],
                    [270, 271],
                    2720,
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                ],
                'code' => [
                    'name' => 'CVC',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_AMERICAN_EXPRESS => [
                'niceType' => 'American Express',
                'type' => CreditCardValidator::TYPE_AMERICAN_EXPRESS,
                'patterns' => [
                    34,
                    37,
                ],
                'gaps' => [4, 10],
                'lengths' => [
                    15,
                ],
                'code' => [
                    'name' => 'CID',
                    'size' => 4,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_DINERS_CLUB => [
                'niceType' => 'Diners Club',
                'type' => CreditCardValidator::TYPE_DINERS_CLUB,
                'patterns' => [
                    [300, 305],
                    36,
                    38,
                    39,
                ],
                'gaps' => [4, 10],
                'lengths' => [
                    14,
                    16,
                    19,
                ],
                'code' => [
                    'name' => 'CVV',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_DISCOVER => [
                'niceType' => 'Discover',
                'type' => CreditCardValidator::TYPE_DISCOVER,
                'patterns' => [
                    6011,
                    [644, 649],
                    65,
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                    19,
                ],
                'code' => [
                    'name' => 'CID',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_JCB => [
                'niceType' => 'JCB',
                'type' => CreditCardValidator::TYPE_JCB,
                'patterns' => [
                    2131,
                    1800,
                    [3528, 3589],
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                    17,
                    18,
                    19,
                ],
                'code' => [
                    'name' => 'CVV',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_UNIONPAY => [
                'niceType' => 'UnionPay',
                'type' => CreditCardValidator::TYPE_UNIONPAY,
                'patterns' => [
                    620,
                    [624, 626],
                    [62100, 62182],
                    [62184, 62187],
                    [62185, 62197],
                    [62200, 62205],
                    [622010, 622999],
                    622018,
                    [622019, 622999],
                    [62207, 62209],
                    [622126, 622925],
                    [623, 626],
                    6270,
                    6272,
                    6276,
                    [627700, 627779],
                    [627781, 627799],
                    [6282, 6289],
                    6291,
                    6292,
                    810,
                    [8110, 8131],
                    [8132, 8151],
                    [8152, 8163],
                    [8164, 8171],
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    14,
                    15,
                    16,
                    17,
                    18,
                    19,
                ],
                'code' => [
                    'name' => 'CVN',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_MAESTRO => [
                'niceType' => 'Maestro',
                'type' => CreditCardValidator::TYPE_MAESTRO,
                'patterns' => [
                    493698,
                    [500000, 504174],
                    [504176, 506698],
                    [506779, 508999],
                    [56, 59],
                    63,
                    67,
                    6,
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    [12, 19],
                ],
                'code' => [
                    'name' => 'CVC',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_ELO => [
                'niceType' => 'Elo',
                'type' => CreditCardValidator::TYPE_ELO,
                'patterns' => [
                    401178,
                    401179,
                    438935,
                    457631,
                    457632,
                    431274,
                    451416,
                    457393,
                    504175,
                    [506699, 506778],
                    [509000, 509999],
                    627780,
                    636297,
                    636368,
                    [650031, 650033],
                    [650035, 650051],
                    [650405, 650439],
                    [650485, 650538],
                    [650541, 650598],
                    [650700, 650718],
                    [650720, 650727],
                    [650901, 650978],
                    [651652, 651679],
                    [655000, 655019],
                    [655021, 655058],
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                ],
                'code' => [
                    'name' => 'CVE',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_MIR => [
                'niceType' => 'Mir',
                'type' => CreditCardValidator::TYPE_MIR,
                'patterns' => [
                    [
                        2200,
                        2204,
                    ],
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                    17,
                    18,
                    19,
                ],
                'code' => [
                    'name' => 'CVP2',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_HIPER => [
                'niceType' => 'Hiper',
                'type' => CreditCardValidator::TYPE_HIPER,
                'patterns' => [
                    637095,
                    63737423,
                    63743358,
                    637568,
                    637599,
                    637609,
                    637612,
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                ],
                'code' => [
                    'name' => 'CVC',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
            CreditCardValidator::TYPE_HIPERCARD => [
                'niceType' => 'Hipercard',
                'type' => CreditCardValidator::TYPE_HIPERCARD,
                'patterns' => [
                    606282,
                ],
                'gaps' => [4, 8, 12],
                'lengths' => [
                    16,
                ],
                'code' => [
                    'name' => 'CVC',
                    'size' => 3,
                ],
                'luhnCheck' => true,
            ],
        ];
    }
}
