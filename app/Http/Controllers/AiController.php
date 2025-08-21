<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\LocalFaqService;
use App\Services\TopicCodeService;
use Illuminate\Http\Request;

class AiController extends Controller
{
  public function __construct(protected AiService $ai) {}

  // // نقطة الأسئلة السياحية
  // public function chat(Request $request)
  // {
  //   $validated = $request->validate([
  //     'query' => ['required', 'string', 'min:3'],
  //     'lang'  => ['nullable', 'string', 'in:ar,en,fr,es,ja,zh'],
  //   ]);

  //   $answer = $this->ai->askTourism(
  //     $validated['query'],
  //     $validated['lang'] ?? 'ar'
  //   );

  //   return response()->json([
  //     'answer' => $answer,
  //   ]);
  // }

  // نقطة إنشاء برنامج الرحلة
  public function itinerary(Request $request)
  {
    $validated = $request->validate([
      'destination' => ['required', 'string', 'min:2'],
      'days'        => ['nullable', 'integer', 'min:1', 'max:21'],
      'budget'      => ['nullable', 'string'],
      'style'       => ['nullable', 'string'],
      'lang'        => ['nullable', 'string', 'in:ar,en,fr,es,ja,zh'],
    ]);

    $plan = $this->ai->buildItinerary(
      $validated['destination'],
      [
        'days'   => $validated['days'] ?? 3,
        'budget' => $validated['budget'] ?? 'متوسط',
        'style'  => $validated['style'] ?? 'مزيج من الثقافة والطبيعة والطعام',
        'lang'   => $validated['lang'] ?? 'ar',
      ]
    );

    return response()->json([
      'plan' => $plan,
    ]);
  }



  // public function chat(Request $request, LocalFaqService $localFaq, AiService $ai)
  // {
  //   $query = $request->input('query', '');

  //   // حاول الإجابة محليًا أولاً
  //   if ($answer = $localFaq->findAnswer($query)) {
  //     return response()->json([
  //       'source' => 'local',
  //       'answer' => $answer,
  //     ]);
  //   }

  //   // إذا ما لقى، نادِ Gemini
  //   $aiAnswer = $ai->askTourism($query);

  //   return response()->json([
  //     'source' => 'gemini',
  //     'answer' => $aiAnswer,
  //   ]);
  // }

  public function chat(
    Request $request,
    TopicCodeService $topicCode,
    LocalFaqService $localFaq,
    AiService $ai
  ) {
    $query = $request->input('query', '');

    // إذا السؤال يطابق كود محدد
    if ($code = $topicCode->detectCode($query)) {
      return response()->json([
        'source' => 'code-map',
        'code' => $code,
      ]);
    }

    // حاول الإجابة محليًا
    if ($answer = $localFaq->findAnswer($query)) {
      return response()->json([
        'source' => 'local',
        'answer' => $answer,
      ]);
    }

    // إذا ما لقى، نادِ Gemini
    $aiAnswer = $ai->askTourism($query);

    return response()->json([
      'source' => 'gemini',
      'answer' => $aiAnswer,
    ]);
  }
}
