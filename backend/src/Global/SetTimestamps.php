<?php

namespace App\Global;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

abstract class SetTimestamps
{
    #[ORM\Column(type: 'carbon', options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?Carbon $createdAt = null;

    #[ORM\Column(type: 'carbon', nullable: true)]
    protected ?Carbon $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = Carbon::now();
        $this->updatedAt = Carbon::now();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = Carbon::now();
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }
}
