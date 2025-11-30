<?php
namespace bng\DTO;

use DateTime;

class ClientDTO
{
    public string $name;
    public string $gender;
    public DateTime $birthdate;
    public string $email;
    public string $phone;
    public string $interests;
    public int $agentId;

    public function __construct(
        string $name,
        string $gender,
        DateTime $birthdate,
        string $email,
        string $phone,
        int $agentId,
        string $interests = ''
    ) {
        $this->name = $name;
        $this->gender = $gender;
        $this->birthdate = $birthdate;
        $this->email = $email;
        $this->phone = $phone;
        $this->agentId = $agentId;
        $this->interests = $interests;
    }
}
