<?php

namespace App\Services;

// class TopicCodeService
// {
//     protected array $topics = [
//         'حقائب'   => 1,
//         'شنط'     => 1,
//         'الحسابات'=> 2,
//         'فاتورة'  => 2,
//         'حجوزات'  => 3,
//         'حجز'     => 3,
//     ];

//     public function detectCode(string $query): ?int
//     {
//         $normalized = mb_strtolower($query);
//         foreach ($this->topics as $keyword => $code) {
//             if (mb_strpos($normalized, mb_strtolower($keyword)) !== false) {
//                 return $code;
//             }
//         }
//         return null;
//     }

//     // لتعديل الأكواد أو الكلمات لاحقًا:
//     public function setCode(string $keyword, int $code): void
//     {
//         $this->topics[$keyword] = $code;
//     }
// }

class TopicCodeService
{
  protected array $topics = [
    'حقائب'   => +963938246910,
    'شنط'     => +963938246910,
    'الحسابات' => 2,
    'فاتورة'  => 2,
    'حجوزات'  => 3,
    'حجز'     => 3,
  ];

  public function detectCode(string $query): ?int
  {
    $normalized = mb_strtolower($query);

    foreach ($this->topics as $keyword => $code) {
      // إذا الكلمة ظهرت في أي موضع من الجملة
      if (mb_strpos($normalized, mb_strtolower($keyword)) !== false) {
        return $code;
      }
    }
    return null;
  }
}
