<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class YourClass
{
    public function storeEventInfo($eventInfo)
    {
        // Almacenar la información del evento en el caché por 10 minutos
        Cache::put('event_info', $eventInfo, 600);
    }

    public function getEventInfo()
    {
        // Obtener la información del evento desde el caché
        return Cache::get('event_info');
    }
}
