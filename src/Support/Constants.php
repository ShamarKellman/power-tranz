<?php

declare(strict_types=1);

namespace Shamarkellman\PowerTranz\Support;

class Constants
{
    public const DRIVER_NAME = 'PowerTranz - Payment Gateway';

    public const PLATFORM_PWT_UAT = 'https://staging.ptranz.com/api/';

    public const PLATFORM_PWT_PROD = 'https://gateway.ptranz.com/api/';

    public const CONFIG_KEY_PWTID = 'PWTId';

    public const CONFIG_KEY_PWTPWD = 'PWTpwd';

    public const CONFIG_KEY_MERCHANT_RESPONSE_URL = 'merchantResponseURL';

    public const CONFIG_KEY_WEBHOOK_URL = 'webHookURL';

    public const AUTHORIZE_OPTION_3DS = 'ThreeDSecure';

    public const GATEWAY_ORDER_IDENTIFIER_PREFIX = 'orderNumberPrefix';

    public const GATEWAY_ORDER_IDENTIFIER_AUTOGEN = 'orderNumberAutoGen';

    public const GATEWAY_ORDER_IDENTIFIER = 'orderIdentifier';

    public const CONFIG_KEY_PWTCUR = 'facCurrencyList';

    public const CONFIG_BILLING_STATE_CODE = 'MB';

    public const CONFIG_CURRENCY_CODE = '780';

    public const CONFIG_COUNTRY_CODE = '780';
}
