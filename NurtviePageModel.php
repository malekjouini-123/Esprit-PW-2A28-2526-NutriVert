<?php
declare(strict_types=1);

/**
 * Entité de configuration de page (modèle données uniquement).
 */
class NurtviePageModel
{
    private string $title = '';
    private string $cssHref = '';
    private string $jsSaisieHref = '';
    private string $jsPageHref = '';
    private string $logoSrc = '';
    private string $adminHref = '';
    private string $initialTheme = 'light';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getCssHref(): string
    {
        return $this->cssHref;
    }

    public function setCssHref(string $cssHref): self
    {
        $this->cssHref = $cssHref;
        return $this;
    }

    public function getJsSaisieHref(): string
    {
        return $this->jsSaisieHref;
    }

    public function setJsSaisieHref(string $jsSaisieHref): self
    {
        $this->jsSaisieHref = $jsSaisieHref;
        return $this;
    }

    public function getJsPageHref(): string
    {
        return $this->jsPageHref;
    }

    public function setJsPageHref(string $jsPageHref): self
    {
        $this->jsPageHref = $jsPageHref;
        return $this;
    }

    public function getLogoSrc(): string
    {
        return $this->logoSrc;
    }

    public function setLogoSrc(string $logoSrc): self
    {
        $this->logoSrc = $logoSrc;
        return $this;
    }

    public function getAdminHref(): string
    {
        return $this->adminHref;
    }

    public function setAdminHref(string $adminHref): self
    {
        $this->adminHref = $adminHref;
        return $this;
    }

    public function getInitialTheme(): string
    {
        return $this->initialTheme;
    }

    public function setInitialTheme(string $initialTheme): self
    {
        $this->initialTheme = $initialTheme;
        return $this;
    }
}
