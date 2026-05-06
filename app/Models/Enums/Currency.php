<?php

namespace App\Models\Enums;

enum Currency: string
{
    case USD = 'USD'; // United States Dollar
    case EUR = 'EUR'; // Euro
    case JPY = 'JPY'; // Japanese Yen
    case GBP = 'GBP'; // British Pound Sterling
    case CHF = 'CHF'; // Swiss Franc
    case CAD = 'CAD'; // Canadian Dollar
    case AUD = 'AUD'; // Australian Dollar
    case CNY = 'CNY'; // Chinese Yuan Renminbi
    case INR = 'INR'; // Indian Rupee
    case BRL = 'BRL'; // Brazilian Real
    case RUB = 'RUB'; // Russian Ruble
    case ZAR = 'ZAR'; // South African Rand
    case MXN = 'MXN'; // Mexican Peso
    case SGD = 'SGD'; // Singapore Dollar
    case HKD = 'HKD'; // Hong Kong Dollar
    case NZD = 'NZD'; // New Zealand Dollar
    case SEK = 'SEK'; // Swedish Krona
    case NOK = 'NOK'; // Norwegian Krone
    case DKK = 'DKK'; // Danish Krone
    case KRW = 'KRW'; // South Korean Won
    case TRY = 'TRY'; // Turkish Lira
    case PLN = 'PLN'; // Polish Zloty
    case THB = 'THB'; // Thai Baht
    case IDR = 'IDR'; // Indonesian Rupiah
    case PHP = 'PHP'; // Philippine Peso
    case MYR = 'MYR'; // Malaysian Ringgit
    case SAR = 'SAR'; // Saudi Riyal
    case AED = 'AED'; // United Arab Emirates Dirham
    case CLP = 'CLP'; // Chilean Peso
    case COP = 'COP'; // Colombian Peso
    case ARS = 'ARS'; // Argentine Peso
    case EGP = 'EGP'; // Egyptian Pound

    /**
     * Returns a human-readable name for the currency.
     * You can expand this method to include more detailed names or symbols.
     *
     * @return string
     */
    public function getName(): string
    {
        return match ($this) {
            self::USD => 'United States Dollar',
            self::EUR => 'Euro',
            self::JPY => 'Japanese Yen',
            self::GBP => 'British Pound Sterling',
            self::CHF => 'Swiss Franc',
            self::CAD => 'Canadian Dollar',
            self::AUD => 'Australian Dollar',
            self::CNY => 'Chinese Yuan Renminbi',
            self::INR => 'Indian Rupee',
            self::BRL => 'Brazilian Real',
            self::RUB => 'Russian Ruble',
            self::ZAR => 'South African Rand',
            self::MXN => 'Mexican Peso',
            self::SGD => 'Singapore Dollar',
            self::HKD => 'Hong Kong Dollar',
            self::NZD => 'New Zealand Dollar',
            self::SEK => 'Swedish Krona',
            self::NOK => 'Norwegian Krone',
            self::DKK => 'Danish Krone',
            self::KRW => 'South Korean Won',
            self::TRY => 'Turkish Lira',
            self::PLN => 'Polish Zloty',
            self::THB => 'Thai Baht',
            self::IDR => 'Indonesian Rupiah',
            self::PHP => 'Philippine Peso',
            self::MYR => 'Malaysian Ringgit',
            self::SAR => 'Saudi Riyal',
            self::AED => 'United Arab Emirates Dirham',
            self::CLP => 'Chilean Peso',
            self::COP => 'Colombian Peso',
            self::ARS => 'Argentine Peso',
            self::EGP => 'Egyptian Pound',
            // Add more cases here for other currencies
        };
    }
}
