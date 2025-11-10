<?php

$columnames_rutes = [
    'id' => 'i',
    'user_email' => 's',
    'origen' => 's',
    'destination' => 's',
    'date_time' => 's',
    'seats' => 'i',
    'description' => 's',
    'available' => 'i',
    'created_at' => 's',
];
class Rutes
{
    private int $id;
    private string $user_email;
    private string $origen;
    private string $destination;
    private string $date_time;
    private int $seats;
    private string $description;
    private int $available;
    private string $created_at;

    public function __construct(
        int $id,
        string $user_email,
        string $origen,
        string $destination,
        string $date_time,
        int $seats,
        string $description,
        int $available,
        string $created_at
    ) {
        $this->id = $id;
        $this->user_email = $user_email;
        $this->origen = $origen;
        $this->destination = $destination;
        $this->date_time = $date_time;
        $this->seats = $seats;
        $this->description = $description;
        $this->available = $available;
        $this->created_at = $created_at;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
}