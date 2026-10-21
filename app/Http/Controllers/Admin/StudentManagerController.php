<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ExamSchedule;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentManagerController extends Controller
{
    public function index(Request $request): Response
    {
        $classId = $request->query('class_id');
        $search = $request->query('search');

        $query = User::where('role', 'student')->with('classRoom');

        if ($classId) {
            $query->where('class_room_id', $classId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('exam_room_name', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(100)->withQueryString();
        $classes = ClassRoom::orderBy('name')->get(['id', 'name', 'grade_level']);

        return Inertia::render('Admin/Students', [
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'class_id' => $classId,
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:4'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'exam_room_name' => ['nullable', 'string', 'max:100'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['username'].'@siswa.smartcbt.sch.id',
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'class_room_id' => $validated['class_room_id'],
            'exam_room_name' => $validated['exam_room_name'] ?? 'Lab Komputer 1',
            'is_active' => true,
        ]);

        return back()->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'password' => ['nullable', 'string', 'min:4'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'exam_room_name' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'class_room_id' => $validated['class_room_id'],
            'exam_room_name' => $validated['exam_room_name'] ?? 'Lab Komputer 1',
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Bulk destroy multiple students
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $count = User::where('role', 'student')->whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "Berhasil menghapus {$count} data siswa terpilih.");
    }

    /**
     * Batch Store students from Quick Paste / Dynamic Table
     */
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'students' => ['required', 'array', 'min:1'],
            'students.*.username' => ['required', 'string', 'max:50'],
            'students.*.name' => ['required', 'string', 'max:255'],
            'students.*.class_room_id' => ['nullable', 'exists:class_rooms,id'],
            'students.*.class_name' => ['nullable', 'string', 'max:100'],
            'students.*.exam_room_name' => ['nullable', 'string', 'max:100'],
            'students.*.password' => ['nullable', 'string', 'min:4'],
        ]);

        $classesCache = ClassRoom::all()->keyBy('name');
        $savedCount = 0;

        foreach ($validated['students'] as $item) {
            $username = trim($item['username'] ?? '');
            $name = trim($item['name'] ?? '');
            if (empty($username) || empty($name)) {
                continue;
            }

            $classId = $item['class_room_id'] ?? null;
            if (! $classId && ! empty($item['class_name'])) {
                $className = trim($item['class_name']);
                $class = $classesCache->get($className);
                if (! $class) {
                    $class = ClassRoom::create([
                        'grade_level' => 10,
                        'major_code' => 'Umum',
                        'name' => $className,
                    ]);
                    $classesCache->put($className, $class);
                }
                $classId = $class->id;
            }

            $password = ! empty($item['password']) ? $item['password'] : 'siswa123';
            $examRoom = ! empty($item['exam_room_name']) ? trim($item['exam_room_name']) : 'Lab Komputer 1';

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $username.'@siswa.smartcbt.sch.id',
                    'password' => Hash::make($password),
                    'role' => 'student',
                    'class_room_id' => $classId,
                    'exam_room_name' => $examRoom,
                    'is_active' => true,
                ]
            );

            $savedCount++;
        }

        return back()->with('success', "Berhasil menambahkan/memperbarui {$savedCount} data siswa.");
    }

    /**
     * Generate bulk student accounts automatically for a class
     */
    public function generateBatch(Request $request)
    {
        $validated = $request->validate([
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'count' => ['required', 'integer', 'min:1', 'max:100'],
            'nisn_prefix' => ['required', 'string', 'max:20'],
            'nisn_start' => ['required', 'integer', 'min:1'],
            'name_prefix' => ['nullable', 'string', 'max:50'],
            'exam_room_name' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:4'],
        ]);

        $class = ClassRoom::findOrFail($validated['class_room_id']);
        $count = (int) $validated['count'];
        $prefix = trim($validated['nisn_prefix']);
        $start = (int) $validated['nisn_start'];
        $namePrefix = trim($validated['name_prefix'] ?? "Siswa {$class->name}");
        $examRoom = trim($validated['exam_room_name'] ?? 'Lab Komputer 1');
        $password = ! empty($validated['password']) ? $validated['password'] : 'siswa123';

        $generated = 0;
        for ($i = 0; $i < $count; $i++) {
            $currentNum = $start + $i;
            $username = $prefix.str_pad((string) $currentNum, 3, '0', STR_PAD_LEFT);
            $name = $namePrefix.' '.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $username.'@siswa.smartcbt.sch.id',
                    'password' => Hash::make($password),
                    'role' => 'student',
                    'class_room_id' => $class->id,
                    'exam_room_name' => $examRoom,
                    'is_active' => true,
                ]
            );

            $generated++;
        }

        return back()->with('success', "Berhasil membuat {$generated} akun siswa otomatis untuk kelas {$class->name}.");
    }

    /**
     * API Inbound Sync for External School Information System (SIS)
     */
    public function apiSync(Request $request)
    {
        $validated = $request->validate([
            'students' => ['required', 'array', 'min:1'],
            'students.*.username' => ['required', 'string', 'max:50'],
            'students.*.name' => ['required', 'string', 'max:255'],
            'students.*.class_name' => ['nullable', 'string'],
            'students.*.exam_room' => ['nullable', 'string'],
            'students.*.password' => ['nullable', 'string'],
        ]);

        $classesCache = ClassRoom::all()->keyBy('name');
        $synced = 0;

        foreach ($validated['students'] as $item) {
            $username = trim($item['username'] ?? '');
            $name = trim($item['name'] ?? '');
            if (empty($username) || empty($name)) {
                continue;
            }

            $className = trim($item['class_name'] ?? '');
            $classId = null;
            if (! empty($className)) {
                $class = $classesCache->get($className);
                if (! $class) {
                    $class = ClassRoom::create([
                        'grade_level' => 10,
                        'major_code' => 'Umum',
                        'name' => $className,
                    ]);
                    $classesCache->put($className, $class);
                }
                $classId = $class->id;
            }

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $username.'@siswa.smartcbt.sch.id',
                    'password' => Hash::make($item['password'] ?? 'siswa123'),
                    'role' => 'student',
                    'class_room_id' => $classId,
                    'exam_room_name' => $item['exam_room'] ?? 'Lab Komputer 1',
                    'is_active' => true,
                ]
            );
            $synced++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menyinkronkan {$synced} data siswa ke CBT.",
            'synced_count' => $synced,
        ]);
    }

    /**
     * Download Excel Template for importing students
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Siswa');

        // Header
        $headers = ['NISN / Username', 'Nama Siswa', 'Kelas (Rombel)', 'Password Default', 'Ruang Ujian'];
        $sheet->fromArray([$headers], null, 'A1');

        // Sample Rows
        $sampleData = [
            ['2026101', 'Bagus Ramadhan', 'X-MIPA-1', 'siswa123', 'Lab Komputer 1'],
            ['2026102', 'Dewi Anggraini', 'X-MIPA-1', 'siswa123', 'Lab Komputer 1'],
            ['2026103', 'Faris Maulana', 'X-MIPA-2', 'siswa123', 'Lab Komputer 2'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Siswa_SmartCBT.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import students mass upload from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Remove header row
        array_shift($rows);

        $imported = 0;
        $classesCache = ClassRoom::all()->keyBy('name');

        foreach ($rows as $row) {
            $username = trim($row[0] ?? '');
            $name = trim($row[1] ?? '');
            $className = trim($row[2] ?? '');
            $password = trim($row[3] ?? 'siswa123');
            $room = trim($row[4] ?? 'Lab Komputer 1');

            if (empty($username) || empty($name)) {
                continue;
            }

            // Find or create classroom
            $class = $classesCache->get($className);
            if (! $class && ! empty($className)) {
                $class = ClassRoom::create([
                    'grade_level' => 10,
                    'major_code' => 'Umum',
                    'name' => $className,
                ]);
                $classesCache->put($className, $class);
            }

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'role' => 'student',
                    'class_room_id' => $class ? $class->id : null,
                    'exam_room_name' => $room,
                    'is_active' => true,
                ]
            );

            $imported++;
        }

        return back()->with('success', "Berhasil mengimpor {$imported} data siswa.");
    }

    /**
     * Print Kartu Peserta Ujian (A4/F4 Print Ready)
     */
    public function printCards(Request $request)
    {
        $classId = $request->query('class_id');
        $query = User::where('role', 'student')->with('classRoom');

        if ($classId) {
            $query->where('class_room_id', $classId);
        }

        $students = $query->orderBy('name')->get();
        $school = SchoolSetting::current();
        $schedules = ExamSchedule::with('subject')->latest()->take(4)->get();

        return view('print.exam_cards', [
            'students' => $students,
            'school' => $school,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Print Daftar Hadir Ruang Ujian
     */
    public function printAttendance(Request $request)
    {
        $room = $request->query('room', 'Lab Komputer 1');
        $query = User::where('role', 'student')->with('classRoom')->where('exam_room_name', $room);

        $students = $query->orderBy('name')->get();
        $school = SchoolSetting::current();

        return view('print.attendance_sheet', [
            'students' => $students,
            'school' => $school,
            'room' => $room,
        ]);
    }
}
