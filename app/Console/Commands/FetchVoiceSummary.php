<?php

namespace App\Console\Commands;

use App\Models\IvrCallHistory;
use App\Models\ProductSale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchVoiceSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-voice-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ivrCallHistory = IvrCallHistory::where('status', 0)->where('date',date('Y-m-d'))->get();
        foreach ($ivrCallHistory as $item) {
            $url = config('voice.voice.base_url') . '/api/v1/voiceSummary';
            $vid = config('voice.voice.vid');
            $api_key = config('voice.voice.api_key');
            $trans_id = $item->trans_id;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $api_key,
            ])->asForm()->post($url, [
                'vid' => $vid,
                'trans_id' => $trans_id
            ]);
            if ($response->successful()) {
                $data = $response->json('data');
                if ($data) {
                    $item->update([
                        'call_received' => $data['call_received'],
                        'duration' => $data['duration'],
                        'response_code' => $data['response_code'],
                        'voice_duration' => $data['voice_duration'],
                        'status' => $data['status'],
                    ]);
                    $Order = Order::where('voice_trans_id', $trans_id)->first();
                    if ($Order) {
                        if($data['response_code']=='1'){
                            $Order->update([
                                'status' => 'Confirmed',
                            ]);
                        }else{
                            $Order->update([
                                'status' => 'Cancel',
                            ]);
                        }
                    }
                    Log::info('Voice summary updated successfully for Trans ID: ' . $trans_id);
                } else {
                    Log::error('Failed to fetch data: ' . $response->body());
                }
            } else {
                Log::error('Failed to fetch data: ' . $response->body());
            }
        }
    }
}
