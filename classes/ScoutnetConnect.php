<?php

namespace Zoomyboy\Scoutnet\Classes;

use Backend;
use Request;
use BackendAuth;
use ApplicationException;
use Zoomyboy\Scoutnet\Plugin;
use Zoomyboy\Scoutnet\Models\Calendar;

class ScoutnetConnect extends Connection {
    public function hasCredentials() {
        return $this->calendar->provider && $this->calendar->aes_key && $this->calendar->aes_iv;
    }

    public static function fromRedirectUri($params, $url): Calendar {
        return Calendar::find($params[0]);
    }

    public function setLogin() {
        if (!Request::filled('auth') || Request::input('logintype') != 'login') {
            throw new ApplicationException('Login fehlgeschlagen.');
        }

        $data = $this->getApi()->getApiKeyFromData();
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
        return Plugin::$loginUrl;
    }

    public function getAuthParams() {
        return [
            'redirect_url' => $this->apiReturnUrl(),
            'lang' => 'de',
            'provider' => $this->calendar->provider,
            'createApiKey' => '1'
        ];
    }

    public function apiReturnUrl() {
        return Backend::url('zoomyboy/scoutnet/calendar/callback/'.static::key().'/'.$this->calendar->id);
    }

    public function saveEvent($event, $user) {
        // @todo save event to scoutnet
    }
}
