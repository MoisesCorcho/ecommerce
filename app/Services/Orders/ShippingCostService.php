<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Orders\UnsupportedShippingDestinationException;
use Illuminate\Support\Str;

/**
 * Resolves shipping costs authoritatively based on market currency and destination zones (F04 & F20).
 */
class ShippingCostService
{
    /**
     * Resolves the shipping cost for a given currency and optional destination.
     *
     * @throws UnsupportedShippingDestinationException
     */
    public function calculate(CurrencyEnum $currency, ?string $country = null, ?string $city = null): int
    {
        $standardCost = $this->standardCost($currency);
        if ($standardCost === 0) {
            return 0;
        }

        $normalizedCountry = $this->normalizeCountry($country);

        // 1. When destination country is missing or empty, fall back to standard currency default
        if ($normalizedCountry === null) {
            return $standardCost;
        }

        $normalizedCity = $this->normalizeCity($city);

        // 2. City-level override within country
        $cityCost = $this->resolveCityOverride($normalizedCountry, $normalizedCity, $currency);
        if ($cityCost !== null) {
            return $cityCost;
        }

        // 3. Country-level national flat rate
        $countryCost = $this->resolveCountryRate($normalizedCountry, $currency);
        if ($countryCost !== null) {
            return $countryCost;
        }

        // 4. Macro-regional international zones
        $regionCost = $this->resolveRegionRate($normalizedCountry, $currency);
        if ($regionCost !== null) {
            return $regionCost;
        }

        // 5. Unlisted destination policy
        if ((bool) config('ecommerce.shipping.zones.allow_unlisted', false)) {
            return (int) config('ecommerce.shipping.zones.unlisted_fallback_cost_usd', 3_000);
        }

        $expectedCurrency = $this->expectedCurrencyForCountry($normalizedCountry);
        if ($expectedCurrency !== null && $expectedCurrency !== $currency && $expectedCurrency->isAvailableInStorefront()) {
            throw UnsupportedShippingDestinationException::currencyMismatch($normalizedCountry, $expectedCurrency);
        }

        throw UnsupportedShippingDestinationException::forCountry($normalizedCountry);
    }

    /**
     * Backward-compatible helper for F04 callers without explicit destination.
     */
    public function standardCost(CurrencyEnum $currency): int
    {
        return $this->defaultCostForCurrency($currency);
    }

    /**
     * Resolves default shipping cost for currency from config.
     */
    public function defaultCostForCurrency(CurrencyEnum $currency): int
    {
        return match ($currency) {
            CurrencyEnum::Cop => (int) config('ecommerce.shipping.standard_cost_cop', config('ecommerce.shipping.zones.countries.CO.cost', 15_000)),
            CurrencyEnum::Eur => (int) config('ecommerce.shipping.standard_cost_eur', config('ecommerce.shipping.zones.regions.europe.cost', 3_000)),
            CurrencyEnum::Usd => (int) config('ecommerce.shipping.standard_cost_usd', config('ecommerce.shipping.zones.regions.americas.cost', 3_000)),
        };
    }

    private function resolveCityOverride(string $country, ?string $citySlug, CurrencyEnum $currency): ?int
    {
        if ($citySlug === null || $citySlug === '') {
            return null;
        }

        /** @var array<string, array{currency?: CurrencyEnum|string, cost: int, aliases?: list<string>}> $cities */
        $cities = (array) config("ecommerce.shipping.zones.cities.{$country}", []);

        foreach ($cities as $key => $rule) {
            $keySlug = Str::slug((string) $key);
            $aliases = array_map(static fn (string $a): string => Str::slug($a), (array) ($rule['aliases'] ?? []));

            if ($citySlug === $keySlug || in_array($citySlug, $aliases, true)) {
                $ruleCurrency = $rule['currency'] ?? null;
                if ($ruleCurrency !== null && $this->normalizeCurrency($ruleCurrency) !== $currency) {
                    return null;
                }

                return (int) ($rule['cost'] ?? 0);
            }
        }

        return null;
    }

    private function resolveCountryRate(string $country, CurrencyEnum $currency): ?int
    {
        $rule = config("ecommerce.shipping.zones.countries.{$country}");

        if (is_array($rule) && isset($rule['cost'])) {
            $ruleCurrency = $rule['currency'] ?? null;
            if ($ruleCurrency !== null && $this->normalizeCurrency($ruleCurrency) !== $currency) {
                return null;
            }

            if ($country === 'CO' && $currency === CurrencyEnum::Cop) {
                $configuredStandard = config('ecommerce.shipping.standard_cost_cop');
                if ($configuredStandard !== null && (int) $configuredStandard !== 15_000) {
                    return (int) $configuredStandard;
                }
            }

            return (int) $rule['cost'];
        }

        return null;
    }

    private function resolveRegionRate(string $country, CurrencyEnum $currency): ?int
    {
        /** @var array<string, array{currency?: CurrencyEnum|string, cost: int, countries: list<string>}> $regions */
        $regions = (array) config('ecommerce.shipping.zones.regions', []);

        foreach ($regions as $regionKey => $region) {
            $countries = array_map('strtoupper', (array) ($region['countries'] ?? []));
            if (in_array($country, $countries, true)) {
                $regionCurrency = $region['currency'] ?? null;
                if ($regionCurrency !== null && $this->normalizeCurrency($regionCurrency) !== $currency) {
                    return null;
                }

                if ($regionKey === 'europe' && $currency === CurrencyEnum::Eur) {
                    $configuredStandard = config('ecommerce.shipping.standard_cost_eur');
                    if ($configuredStandard !== null && (int) $configuredStandard !== 3_000) {
                        return (int) $configuredStandard;
                    }
                }
                if ($regionKey === 'americas' && $currency === CurrencyEnum::Usd) {
                    $configuredStandard = config('ecommerce.shipping.standard_cost_usd');
                    if ($configuredStandard !== null && (int) $configuredStandard !== 3_000) {
                        return (int) $configuredStandard;
                    }
                }

                return (int) ($region['cost'] ?? 0);
            }
        }

        return null;
    }

    /**
     * Resolves the expected market currency for a country if it belongs to any configured shipping zone.
     */
    public function expectedCurrencyForCountry(?string $country): ?CurrencyEnum
    {
        $normalized = $this->normalizeCountry($country);
        if ($normalized === null) {
            return null;
        }

        // 1. Direct country zone
        $countryConfig = config("ecommerce.shipping.zones.countries.{$normalized}");
        if (is_array($countryConfig) && isset($countryConfig['currency'])) {
            return $this->normalizeCurrency($countryConfig['currency']);
        }

        // 2. Macro-regional international zones
        /** @var array<string, array{currency?: CurrencyEnum|string, cost?: int, countries?: list<string>}> $regions */
        $regions = (array) config('ecommerce.shipping.zones.regions', []);
        foreach ($regions as $region) {
            $countries = array_map('strtoupper', (array) ($region['countries'] ?? []));
            if (in_array($normalized, $countries, true)) {
                $currency = $region['currency'] ?? null;
                if ($currency !== null) {
                    return $this->normalizeCurrency($currency);
                }
            }
        }

        // 3. City-level zone fallback
        /** @var array<string, array{currency?: CurrencyEnum|string}> $cities */
        $cities = (array) config("ecommerce.shipping.zones.cities.{$normalized}", []);
        foreach ($cities as $cityRule) {
            $cityCurrency = $cityRule['currency'] ?? null;
            if ($cityCurrency !== null) {
                return $this->normalizeCurrency($cityCurrency);
            }
        }

        return null;
    }

    private function normalizeCurrency(CurrencyEnum|string $currency): CurrencyEnum
    {
        if ($currency instanceof CurrencyEnum) {
            return $currency;
        }

        return CurrencyEnum::from(strtoupper(trim((string) $currency)));
    }

    private function normalizeCountry(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $trimmed = strtoupper(trim($country));

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeCity(?string $city): ?string
    {
        if ($city === null) {
            return null;
        }

        $trimmed = trim($city);
        if ($trimmed === '') {
            return null;
        }

        return Str::slug($trimmed);
    }
}
