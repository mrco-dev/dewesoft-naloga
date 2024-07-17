<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Calendar;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->addScope(Calendar::CALENDAR_READONLY);
    }

    public function login()
    {
        $authUrl = $this->client->createAuthUrl();
        return response()->json(['auth_url' => $authUrl]);
    }

    public function callback(Request $request)
    {
        $this->client->authenticate($request->code);
        $accessToken = $this->client->getAccessToken();
        session(['google_access_token' => $accessToken]);

        \App\Models\AccessToken::updateOrCreate(['id' => 1], ['token' => json_encode($accessToken)]);

        return redirect('/');
    }

    private function fetchAndSaveEvents()
    {
        $accessTokenRecord = \App\Models\AccessToken::find(1);
        $accessToken = $accessTokenRecord ? json_decode($accessTokenRecord->token, true) : null;

        if ($accessToken) {
            $this->client->setAccessToken($accessToken);

            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                $newAccessToken = $this->client->getAccessToken();
                \App\Models\AccessToken::updateOrCreate(['id' => 1], ['token' => json_encode($newAccessToken)]);
            }

            $service = new Calendar($this->client);
            $calendarId = 'primary';

            \App\Models\Event::truncate();

            $events = $service->events->listEvents($calendarId);

            foreach ($events->getItems() as $event) {
                $start = $event->getStart() ? ($event->getStart()->getDateTime() ?: $event->getStart()->getDate()) : null;
                $end = $event->getEnd() ? ($event->getEnd()->getDateTime() ?: $event->getEnd()->getDate()) : null;

                if ($start && $end && $event->getSummary()) {
                    try {
                        Event::updateOrCreate(
                            ['event_id' => $event->getId()],
                            [
                                'summary' => $event->getSummary() ?: '',
                                'description' => $event->getDescription() ?: '',
                                'location' => $event->getLocation() ?: '',
                                'start_time' => $this->parseDateTime($start),
                                'end_time' => $this->parseDateTime($end),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error('Error saving event', [
                            'event_id' => $event->getId(),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function apiEvents()
    {
        if ($this->fetchAndSaveEvents()) {
            $sortedEvents = Event::orderBy('start_time', 'asc')->get();
            return response()->json($sortedEvents);
        }
        return response()->json([], 401);
    }

    public function fetchEvents()
    {
        $this->fetchAndSaveEvents();
    }

    private function parseDateTime($dateTime)
    {
        return Carbon::parse($dateTime)->format('Y-m-d H:i:s');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('google_access_token');
        return response()->json(['message' => 'Logged out successfully']);
    }
}
