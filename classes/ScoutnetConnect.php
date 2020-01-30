<?php

namespace Zoomyboy\Scoutnet\Classes;

use Backend;
use Request;
use BackendAuth;
use ApplicationException;
use Zoomyboy\Scoutnet\Models\Calendar;

class ScoutnetConnect {
    public $calendar;

    public function apiReturnUrl() {
        return Backend::url('zoomyboy/scoutnet/calendar/callback/'.static::key().'/'.$this->calendar->id);
    }

    public static function key() {
        return snake_case(class_basename(static::class));
    }

    public static function fromCalendar(Calendar $calendar) {
        return new static($calendar);
    }

    private function __construct(Calendar $calendar) {
        $this->calendar = $calendar;
    }

    public function isConnected($user = null) {
        $user = $user ?: BackendAuth::getUser();

        if (!$user) { return false; }

        return $this->calendar->credentials()
            ->where('backend_user_id', $user->id)
            ->where('connection', static::key())
            ->exists();
    }


    public function hasCredentials() {
        return $this->calendar->provider && $this->calendar->aes_key && $this->calendar->aes_iv;
    }

    public function buttonText() {
        return $this->isConnected() ? 'zoomyboy.scoutnet::api.'.static::key().'.connected' : 'zoomyboy.scoutnet::api.'.static::key().'.connect';
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

    public function logout() {
        $this->calendar->credentials()->where('backend_user_id', BackendAuth::getUser()->id)->where('connection', static::key())->delete();
    }

    public function getApi() {
        return app('scoutnet.api')->group($this->calendar->scoutnet_id);
    }

}
