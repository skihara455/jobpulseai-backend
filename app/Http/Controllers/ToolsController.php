<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ToolsController extends Controller
{
    /**
     * POST /api/v1/tools/cv-builder
     * Input: name, headline, summary, experience[], education[], skills[]
     * Output: structured sections your UI can render.
     */
    public function cvBuilder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'headline'             => 'nullable|string|max:255',
            'summary'              => 'nullable|string|max:2000',
            'experience'           => 'sometimes|array',
            'experience.*.role'    => 'required_with:experience|string|max:255',
            'experience.*.company' => 'required_with:experience|string|max:255',
            'experience.*.period'  => 'required_with:experience|string|max:255',
            'experience.*.details' => 'nullable|string|max:2000',
            'education'            => 'sometimes|array',
            'education.*.school'   => 'required_with:education|string|max:255',
            'education.*.award'    => 'required_with:education|string|max:255',
            'education.*.year'     => 'nullable|string|max:50',
            'skills'               => 'sometimes|array',
            'skills.*'             => 'string|max:100',
        ]);

        $sections = [
            'header' => [
                'name'     => $data['name'],
                'headline' => $data['headline'] ?? null,
            ],
            'summary' => $data['summary'] ?? 'Motivated professional seeking opportunities to deliver impact.',
            'experience' => array_map(function ($e) {
                return [
                    'title'   => $e['role'],
                    'company' => $e['company'],
                    'period'  => $e['period'],
                    'bullets' => array_filter(preg_split('/\r\n|\r|\n/', $e['details'] ?? '')),
                ];
            }, $data['experience'] ?? []),
            'education' => $data['education'] ?? [],
            'skills'    => $data['skills'] ?? [],
        ];

        return response()->json([
            'success'  => true,
            'template' => 'modern-compact',
            'sections' => $sections,
        ]);
    }

    /**
     * POST /api/v1/tools/ai-job-match
     * Input: skills[] or resume_text
     * Output: recommended jobs from our DB using simple keyword matching.
     */
    public function aiJobMatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'skills'      => 'sometimes|array',
            'skills.*'    => 'string|max:100',
            'resume_text' => 'sometimes|string|max:15000',
            'limit'       => 'sometimes|integer|min:1|max:50',
        ]);

        $limit = $data['limit'] ?? 10;

        $keywords = collect($data['skills'] ?? [])
            ->when(empty($data['skills']) && !empty($data['resume_text']), function ($col) use ($data) {
                $tokens = collect(preg_split('/[^a-zA-Z0-9\+\.#]+/', Str::lower($data['resume_text'])));
                return $col->merge(
                    $tokens->filter(fn($t) => strlen($t) >= 3)->take(15)
                );
            })
            ->unique()
            ->take(10)
            ->values();

        $q = Job::query()->where('status', 'open');

        if ($keywords->isNotEmpty()) {
            $q->where(function ($sub) use ($keywords) {
                foreach ($keywords as $kw) {
                    $kwLike = "%{$kw}%";
                    $sub->orWhere('title', 'like', $kwLike)
                        ->orWhere('tags', 'like', $kwLike)
                        ->orWhere('description', 'like', $kwLike);
                }
            });
        }

        $jobs = $q->orderBy('created_at', 'desc')->limit($limit)->get();

        return response()->json([
            'success'  => true,
            'keywords' => $keywords,
            'results'  => $jobs,
        ]);
    }

    /**
     * POST /api/v1/tools/skill-builder
     * Input: target_role, my_skills[]
     * Output: suggested skill gaps + learning ideas (stubbed).
     */
    public function skillBuilder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'target_role' => 'required|string|max:255',
            'my_skills'   => 'sometimes|array',
            'my_skills.*' => 'string|max:100',
        ]);

        $roleMap = [
            'backend engineer' => ['php', 'laravel', 'mysql', 'rest', 'testing'],
            'frontend engineer'=> ['html', 'css', 'javascript', 'vue', 'testing'],
            'data scientist'   => ['python', 'pandas', 'numpy', 'ml', 'sql'],
        ];

        $target   = Str::lower($data['target_role']);
        $required = collect($roleMap[$target] ?? ['communication', 'problem-solving', 'git']);
        $mine     = collect($data['my_skills'] ?? [])->map(fn($s) => Str::lower($s));
        $gaps     = $required->diff($mine)->values();

        $suggestions = $gaps->map(fn($skill) => [
            'skill' => $skill,
            'ideas' => [
                "Learn {$skill} basics in 7–10 days.",
                "Build a tiny project featuring {$skill}.",
                "Add a bullet point to resume once you ship it.",
            ],
        ]);

        return response()->json([
            'success'     => true,
            'target_role' => $data['target_role'],
            'have'        => $mine->values(),
            'need'        => $gaps,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * POST /api/v1/tools/quiz/submit
     * Input: questions[] (id, answer, correct)
     * Output: score summary (stub).
     */
    public function submitQuiz(Request $request): JsonResponse
    {
        $data = $request->validate([
            'questions'            => 'required|array|min:1',
            'questions.*.id'       => 'required',
            'questions.*.answer'   => 'required|string|max:1000',
            'questions.*.correct'  => 'required|string|max:1000', // real apps store answers server-side
        ]);

        $total   = count($data['questions']);
        $correct = 0;

        foreach ($data['questions'] as $q) {
            if (Str::lower(trim($q['answer'])) === Str::lower(trim($q['correct']))) {
                $correct++;
            }
        }

        return response()->json([
            'success'    => true,
            'total'      => $total,
            'correct'    => $correct,
            'percentage' => round(($correct / max(1, $total)) * 100, 1),
            'feedback'   => $correct >= ($total * 0.7)
                ? 'Great job! Keep going.'
                : 'Review the topics and try again. You’ve got this!',
        ]);
    }
}
