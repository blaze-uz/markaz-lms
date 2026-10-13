<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

/**
 * Trait for models to use dynamic database connection
 *
 * Bu trait modelga qo'shilganda, model avtomatik ravishda
 * hozirgi default connectiondan foydalanadi.
 *
 * MUHIM: Modellarning getConnectionName() methodini override qiladi,
 * bu esa SerializesModels trait bilan yaxshi ishlaydi.
 */
trait UsesDynamicConnection
{
    /**
     * Get the current connection name for the model.
     */
    public function getConnectionName(): ?string
    {
        // Agar model uchun maxsus connection o'rnatilgan bo'lsa, uni ishlat
        if (isset($this->connection) && $this->connection !== null) {
            return $this->connection;
        }

        // Aks holda, hozirgi default connectiondan foydalanамиз
        return Config::get('database.default');
    }

    /**
     * Modelni yangi connection bilan clone qilish
     */
    public function onConnection(?string $connection): static
    {
        $instance = $this->newInstance();
        $instance->setConnection($connection);

        return $instance;
    }
}