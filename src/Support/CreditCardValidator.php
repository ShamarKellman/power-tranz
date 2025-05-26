<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

use BadMethodCallException;
use InvalidArgumentException;

/**
 * Class CreditCardValidator.
 *
 * CreditCard package is used to validate, format and obtain information about
 * a credit card number.
 *
 * Configuration of the credit cards can be found in CreditCardTypes class. Feel
 * free to contribute adding new credit card configurations.
 *
 * You can define the allowed card types on class instantiation by providing an
 * array with the values of the class constants. If you do not provide the
 * array, all the card types of the package will be used to validate card
 * numbers.
 *
 * The most useful methods of the class are isValid($cardNumber) to check the
 * validity of a card number, is($cardType, $cardNumber) to check if a card is
 * from a specific type and getType($cardNumber) to get the
 * CreditCardTypeConfiguration object that matches the given card number. With
 * this object, you can validate the security code of the card, check the Luhn
 * algorithm or format the card number as the expected pretty format for the
 * card.
 *
 * Additionally, you can use the following methods, which are a shortcut to the
 * "is" method.
 *
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
 *
 * @author José Lorente <jose.lorente.martin@gmail.com>
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
     * Map to help magic __call find the correct type for types with special
     * characters.
     *
     * @var array<string, string>
     */
    protected array $methodMap = [
        'americanexpress' => self::TYPE_AMERICAN_EXPRESS,
    ];

    /**
     * Array of credit card configuration objects.
     *
     * @var array
     */
    protected array $typesInfo = [];

    /**
     * @var array
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
     * CreditCardValidator static constructor.
     */
    public static function make(array $allowedTypes = []): static
    {
        return new static($allowedTypes);
    }

    final public function __construct(array $allowedTypes = [])
    {
        if ($allowedTypes) {
            $this->setAllowedTypesList($allowedTypes);
        } else {
            $this->allowedTypes = static::getFullTypesList();
        }
    }

    /**
     * Gets the best CreditCardTypeConfig object that matches the given card number.
     */
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

    /**
     * Checks if the credit card number is valid.
     */
    public function isValid(string $cardNumber): bool
    {
        foreach ($this->getTypesInfo() as $config) {
            if ($config->matches($cardNumber)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the credit card number is a card of the given type.
     *
     * @param string $cardType
     * @param string $cardNumber
     * @return bool
     */
    public function is(string $cardType, string $cardNumber): bool
    {
        $bestMatch = $this->getType($cardNumber);

        return $bestMatch && $bestMatch->getType() === $cardType;
    }

    /**
     * Gets the allowed types list for this object.
     *
     * @return array
     */
    public function getAllowedTypesList(): array
    {
        return $this->allowedTypes;
    }

    /**
     * Set allowed types list for this CreditCardValidator object.
     */
    public function setAllowedTypesList(array $types): void
    {
        $this->allowedTypes = array_intersect($types, static::getFullTypesList());
    }

    /**
     * Gets the credit card typesInfo objects.
     *
     * @return list<CreditCardTypeConfig>
     */
    public function getTypesInfo(): array
    {
        return $this->typesInfo;
    }

    /**
     * Checks if the object has the credit card type configuration.
     *
     * @param  string  $cardType
     * @return bool
     */
    public function hasTypeInfo($cardType): bool
    {
        $typesInfo = $this->getTypesInfo();

        return isset($typesInfo[$cardType]);
    }

    /**
     * Gets the credit card configuration.
     */
    public function getTypeInfo(string $cardType): ?CreditCardTypeConfig
    {
        $typesInfo = $this->getTypesInfo();

        return $typesInfo[$cardType] ?? null;
    }

    /**
     * Magic call support to forward call to is() method.
     *
     * @throws InvalidArgumentException
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments)
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

    /**
     * Loads the credit cards typesInfo.
     */
    protected function loadTypesInfo(): static
    {
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
