<?php

namespace Zoomyboy\Scoutnet\Classes;

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

    public function setLogin() {
        $client = new Client(['base_uri' => 'https://oauth2.googleapis.com']);
        $response = $client->post('/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'code' => request()->code,
                'client_id' => $this->calendar->google_client_id,
                'client_secret' => $this->calendar->google_client_secret,
                'redirect_uri' => $this->apiReturnUrl(),
                'grant_type' => 'authorization_code'
            ]
        ]);

        $data = json_decode((string) $response->getBody());

        $existing = [
            'backend_user_id' => BackendAuth::getUser()->id,
            'connection' => static::key()
        ];

        $this->calendar->credentials()->updateOrCreate($existing, array_merge(compact('data'), $existing));
    }

    public function getApi() {
        return app('scoutnet.api')->group($this->calendar->scoutnet_id);
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
}

