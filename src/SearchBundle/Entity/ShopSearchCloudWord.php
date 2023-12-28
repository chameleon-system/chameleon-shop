<?php

namespace ChameleonSystem\SearchBundle\Entity;


class ShopSearchCloudWord
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Word */
        private string $name = '',
        // TCMSFieldDecimal
        /** @var string - Percentage weight relative to real search terms */
        private string $weight = ''
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCmsident(): ?int
    {
        return $this->cmsident;
    }

    public function setCmsident(int $cmsident): self
    {
        $this->cmsident = $cmsident;

        return $this;
    }

    // TCMSFieldVarchar
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    // TCMSFieldDecimal
    public function getWeight(): string
    {
        return $this->weight;
    }

    public function setWeight(string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }


}
