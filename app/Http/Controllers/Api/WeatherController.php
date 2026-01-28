<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show(Request $request){

        $data = $request->vaildate([
            'city'=>['required','string','max:100'],
            'country'=>['required','string','max:100'],
            'units' => ['nullable','in:metric,imperial'],
        ]);

        $city = trim($data['city']);
        $country = trim($data['country']);
        $units = $data['units'] ?? 'metric';

        $cacheKey ="weather:".strtolower($city).":" .strtolower($country).":".$units;

        $result = Cache::remember($cacheKey,now()->addMinutes(10), function() use ($city,$country,$units){
            $base =rtrim(config('services.openweather.base_url'),'/');
            $key = config('services.openweather.key');

            $res = Http::timeout(8)->get($base .'/weather',[
                'q'=> "{$city},{$country}",
                'appid'=>$key,
                'units'=> $units,
            ]);

            if($res->failed()){
                $msg = res->json('message') ?: 'Failed to fetch weather';
                abort($res->status(), $msg);
            }

            $newdata = $res->json();

            return[
                'location'=>[
                'city'=> $newdata['name'] ?? $city,
                'country'=> $newdata['sys']['country'] ?? $country,
                ],

                'weather'=> [
                    'description' => $newdata['weather'][0]['description'] ?? null,
                    'icon' => $newdata['weather'][0]['icon'] ?? null,
                    'temp' =>$newdata['main']['temp'] ?? null,
                    'feels_like' => $newdata['main']['feels_like'] ?? null,
                    'wind_speed' => $newdata['wind']['speed']?? null,
                ],

                'units'=> $units,
                'fetched_at'=>now()->toIso8601String(),
            ];
        });

        return response()->json([
            'status'=> true,
            'code'=>200,
            'data' =>$result
        ]);
    }
}
