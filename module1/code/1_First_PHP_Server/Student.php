<?php
declare(strict_types=1);

/**
 * A simple student data object used by the Module 1 OOP example.
 */
class Student
{
  public int $id;
  public string $name;
  public string $email;

  public function __construct(int $id, string $name, string $email)
  {
    $this->id = $id;
    $this->name = $name;
    $this->email = $email;
  }

  public function greet(): string
  {
    return "Hi, I'm {$this->name}!";
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
    ];
  }
}
