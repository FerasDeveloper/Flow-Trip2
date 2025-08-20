<?php

namespace App\Services;

class LocalFaqService
{
    protected array $faq = [
        'ما هي عاصمة اليابان' => 'عاصمة اليابان هي طوكيو 🇯🇵',
        'كم عدد أيام الأسبوع' => 'عدد أيام الأسبوع هو سبعة أيام.',
        'ما هو البحر الميت'   => 'البحر الميت بحيرة ملحية تقع بين الأردن وفلسطين المحتلة.',
    ];

    public function findAnswer(string $query): ?string
    {
        $normalized = trim(mb_strtolower($query));
        foreach ($this->faq as $q => $a) {
            if ($normalized === mb_strtolower($q)) {
                return $a;
            }
        }
        return null;
    }
}