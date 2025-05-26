<?php

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
     */
    protected array $patterns = [];

    /**
     * An array of integers used to pretty format the card number values by specifying
     * where are the gaps indexes between the number blocks.
     *
     * e.g.:
     * ```php
     * $gapsA = [4, 8, 12]; // 4242 4242 4242 4242
     * $gapsB = [5, 10]; // 72323 12312 12345
     * ```
     */
    protected array $gaps = [];

    /**
     * An array of integers or arrays values with two elements specifying a range.
     *
     * e.g.:
     * ```php
     * $lengths = [
     *   15,
     *   [10, 13],
     *   [17, 20],
     * ];
     * ```
     */
    protected array $lengths = [];

    /**
     * The configuration of the security code validator (CVV, CVC, CVE, etc.)
     *
     * e.g.:
     * ```php
     * $code = [
     *   'name' => 'CVV',
     *   'size' => 3,
     * ];
     * ```
     *
     * @var array{name: string, size: int}
     */
    protected array $code;

    /**
     * Specifies if the card number should satisfy the luhn check to match the
     * configuration.
     */
    protected bool $luhnCheck = true;

    public function __construct(array $config)
    {
        $this->load($config);
    }

    /**
     * Gets the nice type of the card type configuration (e.g. "Visa", "Mastercard").
     */
    public function getNiceType(): string
    {
        return $this->niceType;
    }

    /**
     * Gets the type of the card type configuration (e.g. "visa", "mastercard").
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the patterns that this card type configuration uses to validate a card number.
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Gets the gaps used by this configuration to pretty format a card number.
     */
    public function getGaps(): array
    {
        return $this->gaps;
    }

    /**
     * Gets the lengths that this card type configuration uses to validate the card numbers.
     */
    public function getLengths(): array
    {
        return $this->lengths;
    }

    /**
     * Gets the security code configuration of the card type.
     *
     * @return array{name: string, size: int}
     */
    public function getCode(): array
    {
        return $this->code;
    }

    /**
     * Gets the luhn check configuration of this object.
     */
    public function getLuhnCheck(): bool
    {
        return $this->luhnCheck;
    }

    /**
     * Sets the luhn check configuration for this object.
     */
    protected function setLuhnCheck(bool $value): static
    {
        $this->luhnCheck = $value;

        return $this;
    }

    /**
     * Checks if the given card number matches this card type configuration.
     */
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

    /**
     * Checks if the card number matches one of the patterns array configuration.
     */
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

    /**
     * Checks if the card number matches one of the patterns array configuration.
     */
    public function matchesPatterns(int|string $cardNumber): bool
    {
        foreach ($this->getPatterns() as $pattern) {
            if ($this->matchesPattern($cardNumber, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the card number matches one of the lengths array configuration.
     */
    public function matchesLengths(string $cardNumber): bool
    {
        foreach ($this->getLengths() as $length) {
            if ($this->matchesLength($cardNumber, $length)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the card number satisfies the luhn's algorithm.
     */
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

    /**
     * Checks whether the security code matches the card type configuration or not.
     */
    public function matchesSecurityCode(string $securityCode): bool
    {
        return $this->isDigits($securityCode) && $this->code['size'] === strlen($securityCode);
    }

    /**
     * Formats the card number according to the gap configuration.
     */
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
     * Loads the card type configuration object.
     */
    protected function load(array $config): static
    {
        if (isset($config['niceType']) === false) {
            throw new InvalidArgumentException('niceType must be provided in the configuration object');
        }

        if (isset($config['type']) === false) {
            throw new InvalidArgumentException('type must be provided in the configuration object');
        }

        if (isset($config['patterns']) === false) {
            throw new InvalidArgumentException('patterns must be provided in the configuration object');
        }

        if (isset($config['gaps']) === false) {
            throw new InvalidArgumentException('gaps must be provided in the configuration object');
        }

        if (isset($config['lengths']) === false) {
            throw new InvalidArgumentException('lengths must be provided in the configuration object');
        }

        if (isset($config['code']) === false) {
            throw new InvalidArgumentException('code must be provided in the configuration object');
        }

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
     * Gets a queue composed by the gaps array for iteration purposes.
     */
    protected function getGapsQueue(): SplQueue
    {
        $queue = new SplQueue;
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

    /**
     * Sets the type of the card type configuration.
     */
    protected function setType(string $value): static
    {
        $this->type = $value;

        return $this;
    }

    /**
     * Sets the patterns used by this configuration to validate a card number.
     *
     * @return $this For fluent configuration of the object.
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

                $value = (int) $value;
            }
        }

        $this->patterns = $values;

        return $this;
    }

    /**
     * Sets the gaps used by this configuration to pretty format a card number.
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
     * Sets the lengths used by this configuration to validate a card number.
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

                $value[0] = (int) $value[0];
                $value[1] = (int) $value[1];

                if ($value[0] < 0 || $value[0] > $value[1]) {
                    throw new InvalidArgumentException('Length range min element should be greater than zero and less than max element');
                }
            } else {
                if ($this->isDigits($value) === false) {
                    throw new InvalidArgumentException('Length elements should be integers or strings representing integer values');
                }

                $value = (int) $value;
            }
        }

        $this->lengths = $values;

        return $this;
    }

    /**
     * Sets the code validator configuration to validate a card number.
     *
     * @throws InvalidArgumentException
     */
    protected function setCode(array $config): static
    {
        if (isset($config['name']) === false || is_string($config['name']) === false) {
            throw new InvalidArgumentException('Code name must be provided in the configuration object and must be a string');
        }

        if (isset($config['size']) === false || $this->isDigits($config['size']) === false) {
            throw new InvalidArgumentException('Code size must be provided in the configuration object and must be an integer or a string representing an integer value');
        }

        $config['size'] = (int) $config['size'];

        $this->code = $config;

        return $this;
    }

    /**
     * Gets the pattern matching strength value.
     */
    protected function getPatternStrength(int|string|array $pattern): int
    {
        if (is_array($pattern)) {
            return min(strlen($pattern[0]), strlen($pattern[1]));
        }

        return strlen($pattern);
    }

    /**
     * Checks if the card number matches the pattern.
     */
    protected function matchesPattern(int|string $cardNumber, int|array|string $pattern): bool
    {
        if (is_array($pattern)) {
            return $this->matchesRange($cardNumber, $pattern[0], $pattern[1]);
        }

        return $this->matchesSimplePattern($cardNumber, $pattern);
    }

    /**
     * Checks if the card number matches a simple pattern.
     */
    protected function matchesSimplePattern(int|string $cardNumber, int|string $pattern): bool
    {
        return substr($cardNumber, 0, strlen($pattern)) === (string) $pattern;
    }

    /**
     * Checks if the card number matches the given pattern range.
     */
    protected function matchesRange(int|string $cardNumber, int|string $min, int|string $max): bool
    {
        $maxLength = max(strlen($min), strlen($max));
        $intCardNumber = (int) substr($cardNumber, 0, $maxLength);
        $intMin = (int) $min;
        $intMax = (int) $max;

        return $intMin <= $intCardNumber && $intCardNumber <= $intMax;
    }

    /**
     * Checks if the card number matches the given length configuration.
     */
    protected function matchesLength(int|string $cardNumber, int|string|array $length): bool
    {
        if (is_array($length)) {
            return $this->matchesLengthRange($cardNumber, $length[0], $length[1]);
        }

        return $this->matchesSimpleLength($cardNumber, $length);
    }

    /**
     * Checks if the card number matches the given scalar length.
     */
    protected function matchesSimpleLength(int|string $cardNumber, int|string $length): bool
    {
        return strlen($cardNumber) === $length;
    }

    /**
     * Checks if the card number matches a length range.
     */
    protected function matchesLengthRange(int|string $cardNumber, int $min, int $max): bool
    {
        $cardNumberLength = strlen($cardNumber);

        return $min <= $cardNumberLength && $cardNumberLength <= $max;
    }

    /**
     * Checks if the value is formed by digits or not.
     */
    protected function isDigits(int|string $value): bool
    {
        return (bool) preg_match('/^[0-9]+$/', (string) $value);
    }
}
