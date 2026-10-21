<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class QuestionBankController extends Controller
{
    public function dashboard(): Response
    {
        $teacher = auth()->user();

        $banksQuery = QuestionBank::with('subject')
            ->withCount('questions')
            ->where(function ($q) use ($teacher) {
                if ($teacher->role === 'teacher') {
                    $q->where('teacher_id', $teacher->id);
                }
            });

        $totalBanks = (clone $banksQuery)->count();
        $totalQuestions = (clone $banksQuery)->get()->sum('questions_count');
        $recentBanks = (clone $banksQuery)->latest()->take(6)->get();

        $schedules = ExamSchedule::with(['subject', 'questionBank'])
            ->withCount(['sessions'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($sch) {
                $attempts = ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $sch->id))->get();
                $sch->total_students = $attempts->count();
                $sch->submitted_count = $attempts->whereIn('status', ['submitted', 'graded'])->count();
                $sch->needs_grading_count = $attempts->where('status', 'submitted')->count();
                $sch->passed_kkm_count = $attempts->where('is_passed_kkm', true)->count();

                return $sch;
            });

        $totalSchedules = ExamSchedule::count();
        $needsGradingTotal = $schedules->sum('needs_grading_count');
        $passedKkmTotal = $schedules->sum('passed_kkm_count');

        $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Teacher/Dashboard', [
            'stats' => [
                'total_banks' => $totalBanks,
                'total_questions' => $totalQuestions,
                'total_schedules' => $totalSchedules,
                'needs_grading_total' => $needsGradingTotal,
                'passed_kkm_total' => $passedKkmTotal,
            ],
            'recentBanks' => $recentBanks,
            'recentSchedules' => $schedules,
            'subjects' => $subjects,
        ]);
    }

    public function index(): Response
    {
        $teacher = auth()->user();

        $banks = QuestionBank::with('subject')
            ->withCount('questions')
            ->where(function ($q) use ($teacher) {
                if ($teacher->role === 'teacher') {
                    $q->where('teacher_id', $teacher->id);
                }
            })
            ->latest()
            ->get();

        $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Teacher/QuestionBanks', [
            'banks' => $banks,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'pg_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'essay_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (($validated['pg_weight_percent'] + $validated['essay_weight_percent']) !== 100) {
            return back()->withErrors(['pg_weight_percent' => 'Total bobot PG dan Esai harus tepat 100%.']);
        }

        $bank = QuestionBank::create([
            ...$validated,
            'teacher_id' => auth()->id(),
        ]);

        return redirect()->route('teacher.banks.show', $bank->id)->with('success', 'Soal ujian berhasil dibuat.');
    }

    public function show(QuestionBank $bank): Response
    {
        $bank->load(['subject', 'questions.options']);

        return Inertia::render('Teacher/QuestionDetail', [
            'bank' => $bank,
        ]);
    }

    public function update(Request $request, QuestionBank $bank)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pg_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'essay_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (($validated['pg_weight_percent'] + $validated['essay_weight_percent']) !== 100) {
            return back()->withErrors(['pg_weight_percent' => 'Total bobot PG dan Esai harus tepat 100%.']);
        }

        $bank->update($validated);

        return back()->with('success', 'Pengaturan soal ujian berhasil diperbarui.');
    }

    public function destroy(QuestionBank $bank)
    {
        $bank->delete();

        return redirect()->route('teacher.banks.index')->with('success', 'Soal ujian berhasil dihapus.');
    }

    /**
     * Store single question with options
     */
    public function storeQuestion(Request $request, QuestionBank $bank)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:single,mcma,true_false,matching,short_answer,essay'],
            'reading_passage' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'max_score' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'options' => ['required_unless:type,short_answer,essay', 'array'],
            'short_answer_key' => ['nullable', 'string'],
            'essay_rubric' => ['nullable', 'string'],
        ]);

        $nextOrder = $bank->questions()->count() + 1;

        $question = Question::create([
            'question_bank_id' => $bank->id,
            'type' => $validated['type'],
            'reading_passage' => $validated['reading_passage'] ?? null,
            'content' => $validated['content'],
            'max_score' => $validated['max_score'],
            'order_index' => $nextOrder,
        ]);

        // Process options based on type
        if (in_array($validated['type'], ['single', 'mcma', 'true_false', 'matching'])) {
            foreach ($request->input('options', []) as $idx => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_label' => $opt['option_label'] ?? chr(65 + $idx),
                    'content' => $opt['content'] ?? '',
                    'matching_pair_text' => $opt['matching_pair_text'] ?? null,
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    'order_index' => $idx + 1,
                ]);
            }
        } elseif ($validated['type'] === 'short_answer') {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'KEY',
                'content' => trim($validated['short_answer_key'] ?? ''),
                'is_correct' => true,
                'order_index' => 1,
            ]);
        } elseif ($validated['type'] === 'essay') {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'RUBRIC',
                'content' => trim($validated['essay_rubric'] ?? 'Kunci/Pedoman Penskoran Guru'),
                'is_correct' => true,
                'order_index' => 1,
            ]);
        }

        return back()->with('success', 'Butir soal berhasil ditambahkan ke naskah ujian.');
    }

    public function destroyQuestion(Question $question)
    {
        $question->delete();

        return back()->with('success', 'Butir soal berhasil dihapus.');
    }

    /**
     * Download Excel Question Template
     */
    public function downloadTemplateExcel()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal CBT');

        $headers = ['Tipe (single/essay)', 'Wacana Kiri (Opsional)', 'Teks Soal', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'Kunci Jawaban', 'Bobot Poin'];
        $sheet->fromArray([$headers], null, 'A1');

        $sampleRows = [
            ['single', '', 'Berapakah hasil dari 12 x 15?', '150', '160', '170', '180', '190', 'D', '10'],
            ['single', 'Indonesia merupakan negara kepulauan terbesar di dunia dengan lebih dari 17.000 pulau.', 'Berdasarkan wacana, Indonesia memiliki lebih dari berapa pulau?', '10.000', '12.000', '15.000', '17.000', '20.000', 'D', '10'],
            ['essay', '', 'Jelaskan apa yang dimaksud dengan fotosintesis dan sebutkan zat yang dihasilkannya!', '', '', '', '', '', 'Rubrik: Menghasilkan oksigen dan glukosa, terjadi pada klorofil dengan bantuan cahaya matahari.', '20'],
        ];
        $sheet->fromArray($sampleRows, null, 'A2');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Soal_SmartCBT.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Questions from Excel
     */
    public function importExcel(Request $request, QuestionBank $bank)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        array_shift($rows); // Remove header

        $imported = 0;
        $startIndex = $bank->questions()->count();

        foreach ($rows as $row) {
            $type = strtolower(trim($row[0] ?? 'single'));
            $passage = trim($row[1] ?? '');
            $content = trim($row[2] ?? '');
            $optA = trim($row[3] ?? '');
            $optB = trim($row[4] ?? '');
            $optC = trim($row[5] ?? '');
            $optD = trim($row[6] ?? '');
            $optE = trim($row[7] ?? '');
            $key = strtoupper(trim($row[8] ?? 'A'));
            $score = (float) ($row[9] ?? 10);

            if (empty($content)) {
                continue;
            }

            $startIndex++;
            $q = Question::create([
                'question_bank_id' => $bank->id,
                'type' => in_array($type, ['single', 'essay']) ? $type : 'single',
                'reading_passage' => ! empty($passage) ? $passage : null,
                'content' => $content,
                'max_score' => $score > 0 ? $score : 10,
                'order_index' => $startIndex,
            ]);

            if ($q->type === 'essay') {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_label' => 'RUBRIC',
                    'content' => $row[8] ?? 'Kunci Rubrik Penilaian',
                    'is_correct' => true,
                    'order_index' => 1,
                ]);
            } else {
                $options = [
                    'A' => $optA,
                    'B' => $optB,
                    'C' => $optC,
                    'D' => $optD,
                    'E' => $optE,
                ];
                $oIndex = 1;
                foreach ($options as $lbl => $text) {
                    if (empty($text)) {
                        continue;
                    }
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_label' => $lbl,
                        'content' => $text,
                        'is_correct' => ($lbl === $key),
                        'order_index' => $oIndex++,
                    ]);
                }
            }

            $imported++;
        }

        return back()->with('success', "Berhasil mengimpor {$imported} soal dari file Excel.");
    }

    /**
     * Import Questions from Word (.docx)
     */
    public function importWord(Request $request, QuestionBank $bank)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:docx,doc'],
        ]);

        $file = $request->file('file');
        $phpWord = WordIOFactory::load($file->getRealPath());

        $textLines = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $txt = $element->getText();
                    if (is_string($txt) && trim($txt) !== '') {
                        $textLines[] = trim($txt);
                    }
                }
            }
        }

        // Parse questions separated by numbers e.g. "1. Soal..."
        $imported = 0;
        $currentQuestion = null;
        $order = $bank->questions()->count();

        foreach ($textLines as $line) {
            if (preg_match('/^(\d+)[\.\)]\s*(.+)/', $line, $matches)) {
                // New question
                $order++;
                $currentQuestion = Question::create([
                    'question_bank_id' => $bank->id,
                    'type' => 'single',
                    'content' => $matches[2],
                    'max_score' => 10.00,
                    'order_index' => $order,
                ]);
                $imported++;
            } elseif ($currentQuestion && preg_match('/^([A-E])[\.\)]\s*(.+)/', $line, $matches)) {
                // Option
                QuestionOption::create([
                    'question_id' => $currentQuestion->id,
                    'option_label' => $matches[1],
                    'content' => $matches[2],
                    'is_correct' => false,
                    'order_index' => ord($matches[1]) - 64,
                ]);
            } elseif ($currentQuestion && preg_match('/^KUNCI:\s*([A-E])/i', $line, $matches)) {
                $k = strtoupper($matches[1]);
                QuestionOption::where('question_id', $currentQuestion->id)
                    ->where('option_label', $k)
                    ->update(['is_correct' => true]);
            }
        }

        return back()->with('success', "Berhasil mengekstrak {$imported} soal dari naskah Word (.docx).");
    }
}
