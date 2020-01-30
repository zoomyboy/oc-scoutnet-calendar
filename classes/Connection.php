<?php

namespace Zoomyboy\Scoutnet\Classes;

use BackendAuth;
use Zoomyboy\Scoutnet\Models\Calendar;

class Connection {
    public $calendar;

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

    public function buttonText() {
        return $this->isConnected() ? 'zoomyboy.scoutnet::api.'.static::key().'.connected' : 'zoomyboy.scoutnet::api.'.static::key().'.connect';
    }

    public function logout() {
        $this->getCredential()->delete();
    }

    public function getCredential() {
        return $this->calendar->credentials()->where('backend_user_id', BackendAuth::getUser()->id)->where('connection', static::key())->first();
    }
}
