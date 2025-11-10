<?php

$columnames_rutes = [
    'msg' => 's',
    'score' => 'i',
];
$columnames_users = [
    'username' => 's',
    'email' => 's',
    'password' => 's',
    'rating' => 'i',
];
class Ratings
{
    private string $msg;
    private int $score;

    public function __construct(string $msg, int $score)
    {
        $this->msg = $msg;
        $this->score = $score;
    }
    public function getMsg(): string
    {
        return $this->msg;
    }
    public function getScore(): int
    {
        return $this->score;
    }
}
class Users
{
    private string $username;
    private string $email;
    private string $password;
    private int $rating = 0;
    /** @var Ratings[] */
    private array $ratings = [];

    // Constructor
    public function __construct(string $username, string $email, string $password)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        // initialize rating based on current ratings (empty at construction)
        $this->rating = $this->CalcRatings();
    }
    // Getters
    public function getUsername(): string
    {
        return $this->username;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    public function getRating(): int
    {
        return $this->rating;
    }
    public function getRatings(): array
    {
        return $this->ratings;
    }
    //Setters
    public function setUsername(string $s): void
    {
        $this->username = $s;
    }
    public function setEmail(string $e): void
    {
        $this->email = $e;
    }
    public function setPassword(string $p): void
    {
        $this->password = $p;
    }
    //Methods
    private function CalcRatings(): int
    {
        if (count($this->ratings) === 0) {
            return 0;
        }
        $totalScore = 0;
        foreach ($this->ratings as $rating) {
            $totalScore += $rating->getScore();
        }
        return (int) ($totalScore / count($this->ratings));
    }
}