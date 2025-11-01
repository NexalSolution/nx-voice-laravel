# nx-voice-laravel

A Laravel package for creating and tracking IVR (Interactive Voice Response) calls, intended for integration with a voice API provider.

---

## Features

- Trigger an IVR call to a customer with a single method.
- Automatically logs and persists the voice API transaction ID.
- Scheduled command to fetch and update IVR call summaries from the provider.
- Built-in error logging for quick troubleshooting.

---

## Installation

1. **Clone or require the package**  
   If this is a standalone codebase, clone or add as a submodule.  
   If distributed via composer, add to your composer file:

   ```bash
   composer require your-vendor/nx-voice-laravel
   ```

2. **Run Migration**  
   Ensure you have the `ivr_call_history` table. Migration sample:

   ```sql
   CREATE TABLE ivr_call_history (
       id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       order_id BIGINT UNSIGNED NULL,
       trans_id BIGINT UNSIGNED NOT NULL,
       msisdn VARCHAR(20) NOT NULL,
       date DATE NOT NULL,
       call_received TINYINT(1) DEFAULT 0,
       duration INT DEFAULT 0,
       response_code VARCHAR(10) NULL,
       voice_duration INT DEFAULT 0,
       status TINYINT(1) DEFAULT 0,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
       INDEX idx_trans_id (trans_id),
       INDEX idx_msisdn (msisdn),
       INDEX idx_order_id (order_id)
   );
   ```

   Or publish and run the provided migration:

   ```bash
   php artisan migrate
   ```

3. **Publish Configuration**  
   If the package provides config publishing:

   ```bash
   php artisan vendor:publish --provider="Vendor\\VoiceServiceProvider"
   ```

4. **Configure `.env`**  
   Add your voice provider credentials:

   ```
   VOICE_BASE_URL=https://your-voice-provider.com
   VOICE_VID=your_voice_vid
   VOICE_VOICE_ID=your_voice_id
   VOICE_API_KEY=your_api_key
   ```

---

## Usage

### Controller Example – Creating an IVR Call

Invoke the IVR call in your controller after a sale is created:

```php
use App\Services\VoiceService;
use Illuminate\Support\Facades\Log;

public function notifyCustomer(Request $request, $saleId)
{
    $sale = Order::findOrFail($saleId);

    // Create IVR call for the customer
    $voiceResponse = (new VoiceService())->createIvrCall(
        $request->phone,
        date('Y-m-d'),
        $sale->id
    );

    if ($voiceResponse['success']) {
        $sale->voice_trans_id = $voiceResponse['trans_id'];
        $sale->save();

        Log::info("Voice Response: " . json_encode($voiceResponse));
    } else {
        Log::error("Voice Error: " . $voiceResponse['error']);
    }
}
```

#### Parameters:
- **phone** : the recipient's phone number.
- **date('Y-m-d')** : current date (format as required by your API).
- **$sale->id** : reference to your sale/order.

#### Returns:
- An array:
  - `success` (boolean)
  - `trans_id` (string, present if success)
  - `error` (string, present if failed)

---

## Background Sync

The command (`app:fetch-voice-summary`) is scheduled to run automatically every minute to update IVR call statuses:

```php
// routes/console.php
Schedule::command('app:fetch-voice-summary')->everyMinute()->description('Fetch voice summary');
```

You can run it manually:

```bash
php artisan app:fetch-voice-summary
```

---

## Logging

- Success and error messages from the voice API are logged automatically via Laravel's logging system.
- Investigate your application's `storage/logs/laravel.log` for message details.

---

## Troubleshooting

- Double-check your `.env` settings and ensure API credentials are correct.
- Make sure migrations have run and the `ivr_call_history` table exists.
- Use the logs to diagnose connectivity or API issues.

---

## Extending

You may extend `VoiceService` or override its methods if you need to customize request payloads or handle additional API responses.

---

## License

[MIT](LICENSE) or as per project.

---

## Credits

- Inspired by real-world voice notification automation needs.
- Maintained by [Mueed Hasan Sarzil].

