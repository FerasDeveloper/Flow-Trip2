<?php

namespace App\Services;

class TopicCodeService
{
  // protected array $topics = [
  //   'حقائب'   => +963938246910,
  //   'شنط'     => +963938246910,
  //   'الحسابات' => 2,
  //   'فاتورة'  => 2,
  //   'حجوزات'  => 3,
  //   'حجز'     => 3,
  // ];

  // public function detectCode(string $query): ?int
  // {
  //   $normalized = mb_strtolower($query);

  //   foreach ($this->topics as $keyword => $code) {
  //     // إذا الكلمة ظهرت في أي موضع من الجملة
  //     if (mb_strpos($normalized, mb_strtolower($keyword)) !== false) {
  //       return $code;
  //     }
  //   }
  //   return null;
  // }


   public function detectCode(string $query): ?int
    {
        $normalized = mb_strtolower($query);

        // 1️⃣ مشكلة + حقائب
        if (mb_strpos($normalized, 'مشكلة') !== false && mb_strpos($normalized, 'حقائب') !== false) {
            return +963938246910; // الرقم الأول
        }

        // 2️⃣ مشكلة + تكت
        if (mb_strpos($normalized, 'مشكلة') !== false && mb_strpos($normalized, 'تكت') !== false) {
            return +963936868467; // الرقم الثاني
        }

        // 3️⃣ مشكلة + حجوزات
        if (mb_strpos($normalized, 'مشكلة') !== false && mb_strpos($normalized, 'حجوزات') !== false) {
            return +963936868467; // الرقم الثاني
        }

        // 4️⃣ تعديل + معلومات شخصية
        if (mb_strpos($normalized, 'تعديل') !== false && mb_strpos($normalized, 'معلومات شخصية') !== false) {
            return +963981693564; // الرقم الثالث
        }

        // 5️⃣ تغير + ايميل
        if (mb_strpos($normalized, 'تغير') !== false && mb_strpos($normalized, 'ايميل') !== false) {
            return +963981693564;
        }

        // 6️⃣ تغير + كلمة سر
        if (mb_strpos($normalized, 'تغير') !== false && mb_strpos($normalized, 'كلمة سر') !== false) {
            return +963981693564;
        }

        // 7️⃣ تغير + رقم هاتف
        if (mb_strpos($normalized, 'تغير') !== false && mb_strpos($normalized, 'رقم هاتف') !== false) {
            return +963981693564;
        }

        // 8️⃣ تغير حسابي
        if (mb_strpos($normalized, 'تغير حسابي') !== false) {
            return +963981693564;
        }

        // 9️⃣ شكوى
        if (mb_strpos($normalized, 'شكوى') !== false) {
            return +963933164121; // الرقم الرابع
        }

        // 🔟 إذا فقط تحتوي على كلمة مشكلة (وما انطبقت الشروط السابقة)
        if (mb_strpos($normalized, 'مشكلة') !== false) {
            return +963959051812; // الرقم الخامس
        }

        // إذا ما في أي تطابق
        return null;
    }

}
