<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CoreTableConfiguration\CmsFieldConf;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopListfilterItemType
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - Filter element class */
        private string $class = '',
        // TCMSFieldVarchar
        /** @var string - Class subtypes of the filter element */
        private string $classSubtype = '',
        // TCMSFieldOption
        /** @var string - Class type of the filter element */
        private string $classType = 'Core',
        // TCMSFieldVarchar
        /** @var string - View of the filter element */
        private string $view = '',
        // TCMSFieldOption
        /** @var string - Class type of the view for the filter element */
        private string $viewClassType = 'Core',
        // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable
        /** @var Collection<int, CmsFieldConf> - Available fields of the filter element */
        private Collection $cmsFieldConfCollection = new ArrayCollection()
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


    // TCMSFieldVarchar
    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }


    // TCMSFieldVarchar
    public function getClassSubtype(): string
    {
        return $this->classSubtype;
    }

    public function setClassSubtype(string $classSubtype): self
    {
        $this->classSubtype = $classSubtype;

        return $this;
    }


    // TCMSFieldOption
    public function getClassType(): string
    {
        return $this->classType;
    }

    public function setClassType(string $classType): self
    {
        $this->classType = $classType;

        return $this;
    }


    // TCMSFieldVarchar
    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }


    // TCMSFieldOption
    public function getViewClassType(): string
    {
        return $this->viewClassType;
    }

    public function setViewClassType(string $viewClassType): self
    {
        $this->viewClassType = $viewClassType;

        return $this;
    }



    // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable

    /**
     * @return Collection<int, CmsFieldConf>
     */
    public function getCmsFieldConfCollection(): Collection
    {
        return $this->cmsFieldConfCollection;
    }

    public function addCmsFieldConfCollection(CmsFieldConf $cmsFieldConfMlt): self
    {
        if (!$this->cmsFieldConfCollection->contains($cmsFieldConfMlt)) {
            $this->cmsFieldConfCollection->add($cmsFieldConfMlt);
            $cmsFieldConfMlt->set($this);
        }

        return $this;
    }

    public function removeCmsFieldConfCollection(CmsFieldConf $cmsFieldConfMlt): self
    {
        if ($this->cmsFieldConfCollection->removeElement($cmsFieldConfMlt)) {
            // set the owning side to null (unless already changed)
            if ($cmsFieldConfMlt->get() === $this) {
                $cmsFieldConfMlt->set(null);
            }
        }

        return $this;
    }


}
