<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class VisitorController extends Controller
{
    // Log visitor only once per day
    public function logVisitor(Request $request)
    {
        $ip = $request->input('ip');
        $today = Carbon::today()->toDateString();

        // Get the country from the IP address using an external API (ip-api)
        $geoData = Http::get("http://ip-api.com/json/{$ip}");

        // Check if the request was successful
        if ($geoData->successful()) {
            $country = $geoData->json()['country']; // Extract country information
        } else {
            $country = 'Unknown'; // Fallback if the geolocation API fails
        }

        // Log the visitor with country information
        if (!$this->visitorExists($ip, $today)) {
            Visitor::create([
                'ip_address' => $ip,
                'country' => $country,
                'visit_date' => $today,
            ]);
        }

        return response()->json(['visitedToday' => false]);
    }

    // Check if visitor has already visited today
    private function visitorExists($ip, $date)
    {
        return Visitor::where('ip_address', $ip)
            ->where('visit_date', $date)
            ->exists();
    }

    // Fetch monthly and daily visitor statistics
    public function getVisitorStats()
    {
        // Fetch monthly visitor stats (group by month and country)
        $monthlyStats = Visitor::selectRaw('country, DATE_FORMAT(visit_date, "%Y-%m") as month, COUNT(DISTINCT ip_address) as visit_count')
            ->groupBy('country', 'month')
            ->get();

        // Fetch daily visitor stats (group by date and country)
        $dailyStats = Visitor::selectRaw('country, visit_date as date, COUNT(DISTINCT ip_address) as visit_count')
            ->groupBy('country', 'date')
            ->get();

        // Format the monthly stats into the required structure
        $monthlyData = [];
        foreach ($monthlyStats as $stat) {
            $monthlyData[] = [
                'country' => $stat->country,
                'date' => $stat->month,
                'visitor' => $stat->visit_count,
            ];
        }

        // Format the daily stats into the required structure
        $dailyData = [];
        foreach ($dailyStats as $stat) {
            $dailyData[] = [
                'country' => $stat->country,
                'date' => $stat->date,
                'visitor' => $stat->visit_count,
            ];
        }

        // Return the structured data in the required format
        return response()->json([
            'monthly' => $monthlyData,
            'daily' => $dailyData,
        ]);
    }
}
