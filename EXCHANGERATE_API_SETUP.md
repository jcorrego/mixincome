# ExchangeRate-API Setup Guide

This application uses **ExchangeRate-API** to fetch Colombian Peso (COP) exchange rates, as the European Central Bank (ECB) does not publish COP rates.

## Getting Your Free API Key

1. Visit [https://www.exchangerate-api.com](https://www.exchangerate-api.com)
2. Click "Get Free Key" or "Start Free"
3. Enter your email address (no credit card required)
4. Verify your email
5. Copy your API key from the dashboard

## Free Tier Limits

- **1,500 requests per month** (50 requests/day)
- Daily rate updates
- 161 currencies supported
- No credit card required
- Perfect for development and MVP

## Configuration

Add your API key to your `.env` file:

```bash
EXCHANGERATE_API_KEY=your_api_key_here
```

## Supported Currency Pairs

The system now supports exchange rates via two services:

### Via ECB (European Central Bank)
- USD ↔ EUR

### Via ExchangeRate-API  
- COP ↔ EUR
- COP ↔ USD
- Any pair involving COP

## Usage in Application

The system automatically routes currency pairs to the appropriate API:

```php
// Automatically uses ECB
$usdEurRate = $fxRateService->fetchRate('USD', 'EUR', $date);

// Automatically uses ExchangeRate-API
$copEurRate = $fxRateService->fetchRate('COP', 'EUR', $date);
$copUsdRate = $fxRateService->fetchRate('COP', 'USD', $date);
```

## Upgrading (Optional)

If you need more requests or faster updates:

**Pro Plan - $10/month**
- 30,000 requests per month
- Hourly updates (instead of daily)
- Priority email support

Visit the [pricing page](https://www.exchangerate-api.com/#pricing) to upgrade.

## Monitoring Usage

Check your usage at: https://app.exchangerate-api.com/dashboard

The free tier tracks:
- Total requests this month
- Requests remaining
- Reset date

## Caching

The application caches exchange rates for 24 hours to minimize API calls. This means:
- Same rate request within 24 hours = no API call
- Fits well within free tier limits
- Reduces latency

## Troubleshooting

**Error: "ExchangeRate-API key not configured"**
- Check your `.env` file has `EXCHANGERATE_API_KEY` set
- Run `php artisan config:clear` to clear config cache

**Error: "ExchangeRate-API error: HTTP 403"**
- Your API key is invalid
- Get a new key from the dashboard

**Error: "ExchangeRate-API error: HTTP 429"**
- You've exceeded free tier limits (1,500/month)
- Wait until next month or upgrade to Pro

## Alternative Free Option

If you prefer not to use an API key, there's a community-maintained alternative:

**Fawazahmed0 Currency API** (No API key required)
- GitHub: https://github.com/fawazahmed0/exchange-api
- No registration needed
- Rate-limited per IP
- Requires fallback mechanism

This is not currently implemented but could be added as a fallback.
