<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

use BadMethodCallException;
use InvalidArgumentException;

/**
 * @method bool isVisa($cardNumber)
 * @method bool isMastercard($cardNumber)
 * @method bool isAmericanExpress($cardNumber)
 * @method bool isDinersClub($cardNumber)
 * @method bool isDiscover($cardNumber)
 * @method bool isJCB($cardNumber)
 * @method bool isUnionPay($cardNumber)
 * @method bool isMaestro($cardNumber)
 * @method bool isElo($cardNumber)
 * @method bool isMir($cardNumber)
 * @method bool isHiper($cardNumber)
 * @method bool isHiperCard($cardNumber)
 */
class CreditCardValidator
{
    public const TYPE_VISA = 'visa';

    public const TYPE_MASTERCARD = 'mastercard';

    public const TYPE_AMERICAN_EXPRESS = 'american-express';

    public const TYPE_DINERS_CLUB = 'diners-club';

    public const TYPE_DISCOVER = 'discover';

    public const TYPE_JCB = 'jcb';

    public const TYPE_UNIONPAY = 'unionpay';

    public const TYPE_MAESTRO = 'maestro';

    public const TYPE_ELO = 'elo';

    public const TYPE_MIR = 'mir';

    public const TYPE_HIPER = 'hiper';

    public const TYPE_HIPERCARD = 'hipercard';

    /**
     * @var array<string, string>
     */
    protected array $methodMap = [
        'americanexpress' => self::TYPE_AMERICAN_EXPRESS,
    ];

    /**
     * @var array<string, CreditCardTypeConfig>
     */
    protected array $typesInfo = [];

    /**
     * @var list<string>
     */
    protected array $allowedTypes = [];

    /**
     * @return list<string>
     */
    public static function getFullTypesList(): array
    {
        return [
            self::TYPE_VISA, self::TYPE_MASTERCARD, self::TYPE_AMERICAN_EXPRESS, self::TYPE_DINERS_CLUB, self::TYPE_DISCOVER, self::TYPE_JCB, self::TYPE_UNIONPAY, self::TYPE_MAESTRO, self::TYPE_ELO, self::TYPE_MIR, self::TYPE_HIPER, self::TYPE_HIPERCARD,
        ];
    }

    /**
     * @param list<string> $allowedTypes
     */
    public static function make(array $allowedTypes = []): static
    {
        return new static($allowedTypes);
    }

    /**
     * @param list<string> $allowedTypes
     */
    final public function __construct(array $allowedTypes = [])
    {
        if ($allowedTypes) {
            $this->setAllowedTypesList($allowedTypes);
        } else {
            $this->allowedTypes = static::getFullTypesList();
        }
    }

    public function getType(int|string $cardNumber): ?CreditCardTypeConfig
    {
        $candidate = null;
        $candidateStrength = 0;

        foreach ($this->getTypesInfo() as $config) {
            if ($config->matches($cardNumber)) {
                $strength = $config->getMatchingPatternStrength($cardNumber);
                if ($strength > $candidateStrength) {
                    $candidate = $config;
                    $candidateStrength = $strength;
                }
            }
        }

        return $candidate;
    }

    public function isValid(string $cardNumber): bool
    {
        foreach ($this->getTypesInfo() as $config) {
            if ($config->matches($cardNumber)) {
                return true;
            }
        }

        return false;
    }

    public function is(string $cardType, string $cardNumber): bool
    {
        $bestMatch = $this->getType($cardNumber);

        return $bestMatch && $bestMatch->getType() === $cardType;
    }

    /**
     * @return list<string>
     */
    public function getAllowedTypesList(): array
    {
        return $this->allowedTypes;
    }

    /**
     * @param list<string> $types
     */
    public function setAllowedTypesList(array $types): void
    {
        $this->allowedTypes = array_intersect($types, static::getFullTypesList());
    }

    /**
     * Gets the credit card typesInfo objects.
     *
     * @return array<string, CreditCardTypeConfig>
     */
    public function getTypesInfo(): array
    {
        return $this->typesInfo;
    }

    public function hasTypeInfo(string $cardType): bool
    {
        $typesInfo = $this->getTypesInfo();

        return isset($typesInfo[$cardType]);
    }

    public function getTypeInfo(string $cardType): CreditCardTypeConfig
    {
        $typesInfo = $this->getTypesInfo();

        return $typesInfo[$cardType];
    }

    /**
     * @param list<mixed> $arguments
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    public function __call(string $name, array $arguments): bool
    {
        if (str_starts_with($name, 'is')) {
            if (isset($arguments[0]) === false) {
                throw new InvalidArgumentException('Card number must be provided');
            }

            $cardNumber = $arguments[0];
            $method = strtolower(substr($name, 2));
            if (isset($this->methodMap[$method])) {
                return $this->is($this->methodMap[$method], $cardNumber);
            }

            return $this->is($method, $cardNumber);
        } else {
            throw new BadMethodCallException("Call to undefined method [$name]");
        }
    }

    protected function loadTypesInfo(): static
    {
        /** @var array<CreditCardTypeConfig> $typesInfo */
        $typesInfo = [];
        $cardTypesList = CreditCardTypeConfigList::get();
        foreach ($this->getAllowedTypesList() as $card) {
            if (isset($cardTypesList[$card])) {
                $typesInfo[$card] = new CreditCardTypeConfig($cardTypesList[$card]);
            }
        }

        $this->typesInfo = $typesInfo;

        return $this;
    }
}
