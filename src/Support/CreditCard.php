<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

class CreditCard
{
    /**
     * Replaces all but the first and last four digits with x's in the given credit card number
     */
    public static function mask(int|string $cc): string
    {
        // replace all digits with X except for the first and last four.
        return preg_replace('/(?!^.?)[0-9](?!(.){0,3}$)/', 'X', $cc);
    }

    /**
     * Add dashes to a credit card number.
     */
    public static function format(int|string $cc): string
    {
        // Clean out extra data that might be in the cc
        $cc = str_replace(['-', ' '], '', $cc);

        // Get the CC Length
        $cc_length = strlen($cc);

        // Initialize the new credit card to contian the last four digits
        $newCreditCard = substr($cc, -4);

        // Walk backwards through the credit card number and add a dash after every fourth digit
        for ($i = $cc_length - 5; $i >= 0; $i--) {
            // If on the fourth character add a dash
            if ((($i + 1) - $cc_length) % 4 == 0) {
                $newCreditCard = '-' . $newCreditCard;
            }
            // Add the current character to the new credit card
            $newCreditCard = $cc[$i] . $newCreditCard;
        }

        // Return the formatted credit card number
        return $newCreditCard;
    }

    /**
     * Remove all non numeric characters from a credit card number
     */
    public static function number(int|string $cc): string
    {
        // remove all non-numeric characters
        return preg_replace('/\D/', '', (string) $cc);
    }
}
