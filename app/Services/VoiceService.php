<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class VoiceService
{
    /**
     * Get configuration values for voice service.
     *
     * @return array
     */
    protected function getConfig(): array
    {
        $config = config('voice.voice');
        return [
            'base_url'  => rtrim($config['base_url'] ?? '', '/'),
            'vid'       => $config['vid'] ?? '',
            'voice_id'  => $config['voice_id'] ?? '',
            'api_key'   => $config['api_key'] ?? '',
        ];
    }

    /**
     * Send POST request to the given URL with headers and form data.
     *
     * @param string $url
     * @param string $api_key
     * @param array $data
     * @return array
     */
    protected function postRequest(string $url, string $api_key, array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $api_key,
            ])->asForm()->post($url, $data);

            if ($response->successful()) {
                $json = $response->json();
                return [
                    'success' => true,
                    'trans_id' => $json['data']['trans_id'] ?? null,
                ];
                Log::info('Voice Service Response: ' . json_encode($json));
            }

            return [
                'success' => false,
                'error'   => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Voice Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Create IVR Call via external API.
     *
     * @param string $msisdn
     * @param string $date
     * @param int $is_feedback
     * @param int $wait_sec
     * @param string|null $vid
     * @param string|null $voice_id
     * @return array
     */
    public function createIvrCall(string $msisdn,string $date,int $order_id = null,int $is_feedback = 1,int $wait_sec = 5): array 
    {
        try {
            $config = $this->getConfig();
            $api_key = $config['api_key'];
            $url = $config['base_url'] . '/api/v1/createIvrCall';
            $data = [
                'vid'        => $config['vid'],
                'voice_id'   => $config['voice_id'],
                'msisdn'     => $msisdn,
                'date'       => $date,
                'is_feedback'=> $is_feedback,
                'wait_sec'   => $wait_sec,
            ];

            $response = $this->postRequest($url, $api_key, $data);
            if ($response['success']) {
                // Insert call history into ivr_call_history table according to @file_context_1 schema.
                \App\Models\IvrCallHistory::create([
                    'order_id' => $order_id,
                    'trans_id' => $response['trans_id'],
                    'msisdn'   => $msisdn,
                    'date'     => $date,
                    'call_received' => 0,
                    'duration' => 0,
                    'response_code' => null,
                    'voice_duration' => 0,
                    'status' => 0,
                ]);
            }
            return $response;
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a Text to Voice IVR call via external API.
     *
     * @param string $msisdn
     * @param string $date
     * @param string $text_to_voice
     * @param int $order_id
     * @return array
     */
    public function createTextToVoiceCall(string $msisdn,string $date,string $text_to_voice,int $order_id=null): array {
        try {
            $config  = $this->getConfig();
            $vid     = $config['vid'];
            $api_key = $config['api_key'];

            $url = $config['base_url'] . '/api/v1/createTextToVoiceCall';
            $data = [
                'vid'           => $vid,
                'text_to_voice' => $text_to_voice,
                'msisdn'        => $msisdn,
                'date'          => $date,
            ];

            $response = $this->postRequest($url, $api_key, $data);
            if ($response['success']) {
                // Insert call history into ivr_call_history table similar to createIvrCall
                \App\Models\IvrCallHistory::create([
                    'order_id' => $order_id,
                    'trans_id' => $response['trans_id'],
                    'msisdn'   => $msisdn,
                    'date'     => $date,
                    'call_received' => 0,
                    'duration' => 0,
                    'response_code' => null,
                    'voice_duration' => 0,
                    'status' => 0,
                ]);
            }
            return $response;
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
