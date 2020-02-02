<?php

namespace Zoomyboy\Scoutnet\Classes;

use DB;
use Backend;
use Request;
use GuzzleHttp\Client;
use BackendAuth;
use ApplicationException;
use Zoomyboy\Scoutnet\Models\Calendar;

class GoogleCalendar extends Connection {
    public function hasCredentials() {
        return $this->calendar->google_client_id && $this->calendar->google_client_secret;
    }

    public static function fromRedirectUri($params, $url): Calendar {
        return Calendar::find(request()->input('state'));
    }

    private function postOauth($data) {
        $client = new Client(['base_uri' => 'https://oauth2.googleapis.com']);
        $response = $client->post('/token', [
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'form_params' => $data
        ]);

        return json_decode((string) $response->getBody());
    }

    public function setLogin() {
        $existing = [
            'backend_user_id' => BackendAuth::getUser()->id,
            'connection' => static::key()
        ];

        $data = $this->postOauth([
            'code' => request()->code,
            'client_id' => $this->calendar->google_client_id,
            'client_secret' => $this->calendar->google_client_secret,
            'redirect_uri' => $this->apiReturnUrl(),
            'grant_type' => 'authorization_code'
        ]);

        $this->calendar->credentials()->updateOrCreate($existing, array_merge(compact('data'), $existing));
    }

    public function getAuthUrl() {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    public function getAuthParams() {
        return [
            'scope' => 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'response_type' => 'code',
            'state' => $this->calendar->id,
            'client_id' => $this->calendar->google_client_id,
            'redirect_uri' => $this->apiReturnUrl()
        ];
    }

    public function apiReturnUrl() {
        return Backend::url('zoomyboy/scoutnet/calendar/callback/'.static::key());
    }

    public function getCalendars() {
        if (!$this->isConnected()) { return []; }
        $this->refresh($this->getCredential());

        $client = new Client(['base_uri' => 'https://www.googleapis.com']);
        $response = $client->get('/calendar/v3/users/me/calendarList', [
            'headers' => [ 'Authorization' => 'Bearer '.$this->getCredential()->data['access_token'] ]
        ]);

        $response = json_decode((string) $response->getBody());
        return collect($response->items)->pluck('summary', 'id')->toArray();
    }

    public function storeCalendar($calendar) {
        $data = $this->getCredential()->data;
        $data['calendar'] = $calendar;
        $this->getCredential()->update(['data' => $data]);
    }

    public function currentCalendar() {
        return $this->getCredential()->data['calendar'] ?? '';
    }

    private function formatDate($event, $d) {
        if ($event->is_all_day) {
            return ['date' => $event->{$d}->format('Y-m-d')];
        }

        return ['dateTime' => $event->{$d}->toRfc3339String()];
    }

    private function refresh($credential) {
        if($credential->updated_at->addSeconds($credential->data['expires_in'] - 10)->isFuture()) {
            return;
        }

        $response = $this->postOauth([
            'client_id' => $this->calendar->google_client_id,
            'client_secret' => $this->calendar->google_client_secret,
            'refresh_token' => $credential->data['refresh_token'],
            'grant_type' => 'refresh_token'
        ]);

        $data = $credential->data;
        $data['access_token'] = $response->access_token;
        $credential->update(['data' => $data]);
    }

    public function saveEvent($event, $user) {
        foreach ($event->calendar->credentials()->where('connection', static::key())->get() as $credential) {
            $this->refresh($credential);

            $client = new Client([
                'base_uri' => 'https://www.googleapis.com',
                'headers' => [ 'Authorization' => 'Bearer '.$credential->data['access_token'] ]
            ]);

            $synch = DB::table('zoomyboy_google_events')
                ->where('event_id', $event->id)
                ->where('credential_id', $credential->id)->first();

            if (is_null($synch)) {
                $response = $client->post('/calendar/v3/calendars/'.$credential->data['calendar'].'/events', [
                    'json' => [
                        'end' => $this->formatDate($event, 'ends_at'),
                        'start' => $this->formatDate($event, 'starts_at'),
                        'location' => $event->location ?: '',
                        'summary' => $event->title
                    ]
                ]);

                $response = json_decode((string) $response->getBody());
                DB::table('zoomyboy_google_events')->insert(['event_id' => $event->id, 'credential_id' => $credential->id, 'google_id' => $response->id]);
            } else {
                $response = $client->put('/calendar/v3/calendars/'.$credential->data['calendar'].'/events/'.$synch->google_id, [
                    'json' => [
                        'end' => $this->formatDate($event, 'ends_at'),
                        'start' => $this->formatDate($event, 'starts_at'),
                        'location' => $event->location ?: '',
                        'summary' => $event->title
                    ]
                ]);
            }
        }
    }
}

