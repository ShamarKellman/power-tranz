<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

use InvalidArgumentException;
use SplQueue;

/**
 * Class CreditCardTypeConfig.
 *
 * Class used to validate and store the configuration of a credit card type.
 *
 * The configuration can be loaded using the load method and providing a valid
 * configuration array.
 *
 * e.g.:
 * ```php
 * $config = new CreditCardTypeConfig([
 *     'niceType' => 'Visa',
 *     'type' => 'visa',
 *     'patterns' => [
 *         4,
 *     ],
 *     'gaps' => [4, 8, 12],
 *     'lengths' => [
 *         16,
 *         18,
 *         19,
 *     ],
 *     'code' => [
 *         'name' => 'CVV',
 *         'size' => 3,
 *     ],
 * ]);
 * ```
 *
 * @see CreditCardTypeConfigList
 *
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class CreditCardTypeConfig
{
    protected string $niceType;

    protected string $type;

    /**
     * An array of integers or arrays values with two elements specifying a range.
     *
     * e.g.:
     * ```php
     * $patterns = [
     *   50,
     *   [5212, 6234],
     *   [743335, 872344],
     * ];
     * ```
     *
     * @var array<int|array<int, int|string>>
     */
    protected array $patterns = [];

    /**
     * @var list<int>
     */
    protected array $gaps = [];

    /**
     * @var array<int|array<int, int>>
     */
    protected array $lengths = [];

    /**
     * @var array{name: string, size: int}
     */
    protected array $code;

    protected bool $luhnCheck = true;

    /**
     * @param array{
     *     niceType: string,
     *     type: string,
     *     patterns: array<int|array<int, int|string>>,
     *     gaps: list<int>,
     *     lengths: array<int|array<int, int>>,
     *     code: array{name: string, size: int},
     *     luhnCheck?: bool
     * } $config
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $this->load($config);
    }

    public function getNiceType(): string
    {
        return $this->niceType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<int|array<int, int|string>>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @return list<int>
     */
    public function getGaps(): array
    {
        return $this->gaps;
    }

    /**
     * @return array<int|array<int, int>>
     */
    public function getLengths(): array
    {
        return $this->lengths;
    }

    /**
     * @return array{name: string, size: int}
     */
    public function getCode(): array
    {
        return $this->code;
    }

    public function getLuhnCheck(): bool
    {
        return $this->luhnCheck;
    }

    protected function setLuhnCheck(bool $value): static
    {
        $this->luhnCheck = $value;

        return $this;
    }

    public function matches(int|string $cardNumber): bool
    {
        if ($this->matchesLengths($cardNumber) === false) {
            return false;
        }

        if ($this->getLuhnCheck() && $this->satisfiesLuhn($cardNumber) === false) {
            return false;
        }

        return $this->matchesPatterns($cardNumber);
    }

    public function getMatchingPatternStrength(int|string $cardNumber): int
    {
        $strength = 0;
        foreach ($this->getPatterns() as $pattern) {
            if ($this->matchesPattern($cardNumber, $pattern)) {
                $s = $this->getPatternStrength($pattern);
                if ($s > $strength) {
                    $strength = $s;
                }
            }
        }

        return $strength;
    }

    public function matchesPatterns(int|string $cardNumber): bool
    {
        foreach ($this->getPatterns() as $pattern) {
            if ($this->matchesPattern($cardNumber, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function matchesLengths(string $cardNumber): bool
    {
        foreach ($this->getLengths() as $length) {
            if ($this->matchesLength($cardNumber, $length)) {
                return true;
            }
        }

        return false;
    }

    public function satisfiesLuhn(int|string $cardNumber): bool
    {
        $cardNumber = preg_replace('/\D/', '', (string) $cardNumber); // Remove non-digits

        $sum = 0;
        $alt = false;

        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $n = (int) $cardNumber[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = ! $alt;
        }

        return $sum % 10 === 0;
    }

    public function matchesSecurityCode(string $securityCode): bool
    {
        return $this->isDigits($securityCode) && $this->code['size'] === strlen($securityCode);
    }

    public function format(int|string $cardNumber): string
    {
        $gaps = $this->getGapsQueue();

        $formatted = '';
        $gap = $gaps->dequeue();
        for ($i = 0, $l = strlen($cardNumber); $i < $l; $i += 1) {
            if ($i === $gap) {
                $formatted .= ' ';
                $gap = $gaps->isEmpty() === false ? $gaps->dequeue() : null;
            }

            $formatted .= $cardNumber[$i];
        }

        return $formatted;
    }

    /**
     * @param array{
     *     niceType: string,
     *     type: string,
     *     patterns: array<int|array<int, int|string>>,
     *     gaps: list<int>,
     *     lengths: array<int|array<int, int>>,
     *     code: array{name: string, size: int},
     *     luhnCheck?: bool
     * } $config
     *
     * @throws InvalidArgumentException
     */
    protected function load(array $config): static
    {
        if (isset($config['luhnCheck']) === false) {
            throw new InvalidArgumentException('luhnCheck must be provided in the configuration object');
        }

        $this->setNiceType($config['niceType'])
            ->setType($config['type'])
            ->setPatterns($config['patterns'])
            ->setGaps($config['gaps'])
            ->setLengths($config['lengths'])
            ->setCode($config['code'])
            ->setLuhnCheck($config['luhnCheck']);

        return $this;
    }

    /**
     * @return SplQueue<int> A queue of gaps.
     */
    protected function getGapsQueue(): SplQueue
    {
        $queue = new SplQueue();
        foreach ($this->getGaps() as $gap) {
            $queue->enqueue($gap);
        }

        return $queue;
    }

    /**
     * Sets the nice type of the card type configuration.
     */
    protected function setNiceType(string $value): static
    {
        $this->niceType = $value;

        return $this;
    }

    protected function setType(string $value): static
    {
        $this->type = $value;

        return $this;
    }

    /**
     * @param array<int|array<int, int|string>> $values
     *
     * @throws InvalidArgumentException
     */
    protected function setPatterns(array $values): static
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                if (count($value) !== 2) {
                    throw new InvalidArgumentException('Pattern elements provided as array range should contain exactly two elements');
                }

                if ($this->isDigits($value[0]) === false || $this->isDigits($value[1]) === false) {
                    throw new InvalidArgumentException('Pattern range elements should be integers or strings representing integer values');
                }

                $value[0] = (int) $value[0];
                $value[1] = (int) $value[1];

                if ($value[0] < 0 || $value[0] > $value[1]) {
                    throw new InvalidArgumentException('Pattern range min element should be greater than zero and less than max element');
                }
            } else {
                if ($this->isDigits($value) === false) {
                    throw new InvalidArgumentException('Pattern elements should be integers or strings representing integer values');
                }
            }
        }

        $this->patterns = $values;

        return $this;
    }

    /**
     * @param list<int> $values
     *
     * @throws InvalidArgumentException
     */
    protected function setGaps(array $values): static
    {
        foreach ($values as $value) {
            if ($this->isDigits($value) === false) {
                throw new InvalidArgumentException('Gaps elements should be integers or strings representing integer values');
            }
        }

        $this->gaps = $values;

        return $this;
    }

    /**
     * @param array<int|array<int, int>> $values
     *
     * @throws InvalidArgumentException
     */
    protected function setLengths(array $values): static
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                if (count($value) !== 2) {
                    throw new InvalidArgumentException('Length elements provided as array range should contain exactly two elements');
                }

                if ($this->isDigits($value[0]) === false || $this->isDigits($value[1]) === false) {
                    throw new InvalidArgumentException('Length range elements should be integers or strings representing integer values');
                }

                if ($value[0] < 0 || $value[0] > $value[1]) {
                    throw new InvalidArgumentException('Length range min element should be greater than zero and less than max element');
                }
            } else {
                if ($this->isDigits($value) === false) {
                    throw new InvalidArgumentException('Length elements should be integers or strings representing integer values');
                }

            }
        }

        $this->lengths = $values;

        return $this;
    }

    /**
     * @param array{name: string, size: int} $config
     */
    protected function setCode(array $config): static
    {
        $config['size'] = (int) $config['size'];

        $this->code = $config;

        return $this;
    }

    /**
     * @param int|string|list<string> $pattern
     */
    protected function getPatternStrength(int|string|array $pattern): int
    {
        if (is_array($pattern)) {
            return min(strlen($pattern[0]), strlen($pattern[1]));
        }

        return strlen($pattern);
    }

    /**
     * @param int|list<string>|string $pattern
     */
    protected function matchesPattern(int|string $cardNumber, int|array|string $pattern): bool
    {
        if (is_array($pattern)) {
            return $this->matchesRange($cardNumber, $pattern[0], $pattern[1]);
        }

        return $this->matchesSimplePattern($cardNumber, $pattern);
    }

    protected function matchesSimplePattern(int|string $cardNumber, int|string $pattern): bool
    {
        return str_starts_with($cardNumber, (string) $pattern);
    }

    protected function matchesRange(int|string $cardNumber, int|string $min, int|string $max): bool
    {
        $maxLength = max(strlen($min), strlen($max));
        $intCardNumber = (int) substr($cardNumber, 0, $maxLength);
        $intMin = (int) $min;
        $intMax = (int) $max;

        return $intMin <= $intCardNumber && $intCardNumber <= $intMax;
    }

    /**
     * @param int|string|array<int, int|string> $length
     */
    protected function matchesLength(int|string $cardNumber, int|string|array $length): bool
    {
        if (is_array($length)) {
            return $this->matchesLengthRange($cardNumber, $length[0], $length[1]);
        }

        return $this->matchesSimpleLength($cardNumber, $length);
    }

    protected function matchesSimpleLength(int|string $cardNumber, int|string $length): bool
    {
        return strlen($cardNumber) === $length;
    }

    protected function matchesLengthRange(int|string $cardNumber, int $min, int $max): bool
    {
        $cardNumberLength = strlen($cardNumber);

        return $min <= $cardNumberLength && $cardNumberLength <= $max;
    }

    protected function isDigits(int|string $value): bool
    {
        return (bool) preg_match('/^[0-9]+$/', (string) $value);
    }
}
