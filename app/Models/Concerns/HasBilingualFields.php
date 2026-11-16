<?php

namespace App\Models\Concerns;

/**
 * Konten dua bahasa dengan pasangan kolom {kolom}_id / {kolom}_en.
 * localized('judul') memakai bahasa aktif; bila kosong, memakai bahasa lain.
 */
trait HasBilingualFields
{
    /** @return list<string> */
    public function bilingualFields(): array
    {
        return static::BILINGUAL;
    }

    public function localized(string $field, ?string $locale = null): ?string
    {
        $locale = $locale === 'en' || ($locale === null && app()->getLocale() === 'en') ? 'en' : 'id';
        $other = $locale === 'en' ? 'id' : 'en';
        $value = trim((string) $this->getAttribute($field.'_'.$locale));

        return $value !== '' ? $value : (trim((string) $this->getAttribute($field.'_'.$other)) ?: null);
    }

    /** True bila setiap kolom dua bahasa yang terisi di satu bahasa juga terisi di bahasa lain. */
    public function translationComplete(): bool
    {
        foreach (static::BILINGUAL as $field) {
            $id = trim((string) $this->getAttribute($field.'_id'));
            $en = trim((string) $this->getAttribute($field.'_en'));
            if (($id === '') !== ($en === '')) {
                return false;
            }
        }

        return true;
    }
}
