<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobileCatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['country' => ['sometimes', 'required', 'in:eg,qa']]);
        $country = $filters['country'] ?? 'eg';
        $source = json_decode(file_get_contents(resource_path('catalog/mobile-demo.json')), true, 512, JSON_THROW_ON_ERROR);
        $academies = [];
        foreach ($source['academies'] as $academy) {
            if ($academy['country'] !== $country) continue;
            $image = match ($academy['selected_sport']) {
                'swimming' => 'swimming', 'padel', 'tennis', 'badminton', 'squash' => 'padel', default => 'discover',
            };
            $academies[] = [
                'key' => $academy['key'], 'name' => $academy['name'], 'name_ar' => $academy['name_ar_proposed'],
                'sport_ar' => $academy['sport_ar'], 'sport_en' => $academy['sport_en'],
                'country' => $country, 'currency' => $academy['currency'], 'logo_url' => null,
                'illustration_url' => rtrim(config('app.url'), '/')."/media/hagzz/brand-v1/{$image}.png",
                'location' => $academy['demo_location']['name_ar'], 'coach' => $academy['demo_coach']['name_ar'],
                'is_demo' => true, 'bookable' => false,
                'trainings' => array_map(fn ($training) => [
                    'key' => $training['key'], 'name_ar' => $training['name_ar'], 'name_en' => $training['name_en'],
                    'price' => $training['proposed_price'], 'currency' => $training['currency'],
                    'sessions' => $training['proposed_sessions'], 'weekdays_ar' => $training['weekdays_ar'],
                    'weekdays_en' => implode(', ', $training['weekdays']),
                    'start_time' => $training['start_time'], 'end_time' => $training['end_time'],
                    'start_date' => $training['start_date'], 'end_date' => $training['end_date'],
                    'timezone' => $training['timezone'], 'is_demo' => true, 'bookable' => false,
                ], $academy['trainings']),
            ];
        }
        return response()->json(['status' => 200, 'data' => [
            'country' => $country, 'is_demo' => true, 'bookable' => false,
            'notice_ar' => 'محتوى تجريبي: الأسعار والمدربون والمواقع والمواعيد غير مؤكدة من الجهات. الحجز والدفع غير متاحين.',
            'notice_en' => 'Demo catalog. Programs, prices, coaches, locations and schedules are illustrative and unverified. No bookings or payments.',
            'academies_count' => count($academies), 'trainings_count' => count($academies) * 3, 'academies' => $academies,
        ]]);
    }
}
