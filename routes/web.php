<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ExamScheduleController;
use App\Http\Controllers\Admin\MasterAcademicController;
use App\Http\Controllers\Admin\StudentManagerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Proctor\ProctorController;
use App\Http\Controllers\Student\StudentExamController;
use App\Http\Controllers\Teacher\GradingController;
use App\Http\Controllers\Teacher\QuestionBankController;
use Illuminate\Support\Facades\Route;

// Public & Authentication
Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'proctor' => redirect()->route('proctor.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');

    // Rombel / Kelas
    Route::get('/classes', [MasterAcademicController::class, 'classRooms'])->name('classes.index');
    Route::post('/classes', [MasterAcademicController::class, 'storeClassRoom'])->name('classes.store');
    Route::put('/classes/{classRoom}', [MasterAcademicController::class, 'updateClassRoom'])->name('classes.update');
    Route::delete('/classes/{classRoom}', [MasterAcademicController::class, 'destroyClassRoom'])->name('classes.destroy');

    // Mata Pelajaran
    Route::get('/subjects', [MasterAcademicController::class, 'subjects'])->name('subjects.index');
    Route::get('/legacy-subjects', [MasterAcademicController::class, 'subjects'])->name('subjects.legacy');
    Route::post('/subjects', [MasterAcademicController::class, 'storeSubject'])->name('subjects.store');
    Route::put('/subjects/{subject}', [MasterAcademicController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [MasterAcademicController::class, 'destroySubject'])->name('subjects.destroy');

    // Guru & Pengawas
    Route::get('/staff', [MasterAcademicController::class, 'staff'])->name('staff.index');
    Route::post('/staff', [MasterAcademicController::class, 'storeStaff'])->name('staff.store');
    Route::put('/staff/{user}', [MasterAcademicController::class, 'updateStaff'])->name('staff.update');
    Route::delete('/staff/{user}', [MasterAcademicController::class, 'destroyStaff'])->name('staff.destroy');

    // Siswa & Cetak Kartu / Daftar Hadir
    Route::get('/students', [StudentManagerController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentManagerController::class, 'store'])->name('students.store');
    Route::post('/students/batch', [StudentManagerController::class, 'batchStore'])->name('students.batchStore');
    Route::post('/students/generate', [StudentManagerController::class, 'generateBatch'])->name('students.generateBatch');
    Route::put('/students/{user}', [StudentManagerController::class, 'update'])->name('students.update');
    Route::delete('/students/{user}', [StudentManagerController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/bulk-destroy', [StudentManagerController::class, 'bulkDestroy'])->name('students.bulkDestroy');
    Route::get('/students/template', [StudentManagerController::class, 'downloadTemplate'])->name('students.template');
    Route::post('/students/import', [StudentManagerController::class, 'import'])->name('students.import');
    Route::get('/students/print-cards', [StudentManagerController::class, 'printCards'])->name('students.printCards');
    Route::get('/students/print-attendance', [StudentManagerController::class, 'printAttendance'])->name('students.printAttendance');

    // Jadwal Ujian & Sesi Ruang
    Route::get('/schedules', [ExamScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ExamScheduleController::class, 'storeSchedule'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [ExamScheduleController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [ExamScheduleController::class, 'destroySchedule'])->name('schedules.destroy');

    Route::post('/schedules/{schedule}/sessions', [ExamScheduleController::class, 'storeSession'])->name('sessions.store');
    Route::put('/sessions/{session}', [ExamScheduleController::class, 'updateSession'])->name('sessions.update');
    Route::post('/sessions/{session}/regenerate-token', [ExamScheduleController::class, 'regenerateToken'])->name('sessions.regenerateToken');
    Route::delete('/sessions/{session}', [ExamScheduleController::class, 'destroySession'])->name('sessions.destroy');
});

// ==================== TEACHER ROUTES ====================
Route::middleware(['auth', 'role:teacher,admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [QuestionBankController::class, 'dashboard'])->name('dashboard');

    // Soal Ujian
    Route::get('/banks', [QuestionBankController::class, 'index'])->name('banks.index');
    Route::post('/banks', [QuestionBankController::class, 'store'])->name('banks.store');
    Route::get('/banks/{bank}', [QuestionBankController::class, 'show'])->name('banks.show');
    Route::put('/banks/{bank}', [QuestionBankController::class, 'update'])->name('banks.update');
    Route::delete('/banks/{bank}', [QuestionBankController::class, 'destroy'])->name('banks.destroy');

    Route::post('/banks/{bank}/questions', [QuestionBankController::class, 'storeQuestion'])->name('questions.store');
    Route::delete('/questions/{question}', [QuestionBankController::class, 'destroyQuestion'])->name('questions.destroy');

    Route::get('/template/excel', [QuestionBankController::class, 'downloadTemplateExcel'])->name('template.excel');
    Route::post('/banks/{bank}/import-excel', [QuestionBankController::class, 'importExcel'])->name('import.excel');
    Route::post('/banks/{bank}/import-word', [QuestionBankController::class, 'importWord'])->name('import.word');

    // Penilaian, Speed-Grading Workbench, Analisis Butir Soal & Leger e-Rapor
    Route::get('/grading', [GradingController::class, 'index'])->name('grading.index');
    Route::get('/grading/{schedule}/workbench', [GradingController::class, 'workbench'])->name('grading.workbench');
    Route::post('/grading/answer/{answer}', [GradingController::class, 'saveEssayScore'])->name('grading.saveScore');
    Route::get('/grading/{schedule}/analysis', [GradingController::class, 'itemAnalysis'])->name('grading.analysis');
    Route::post('/grading/{schedule}/remedial', [GradingController::class, 'createRemedialSession'])->name('grading.remedial');
    Route::get('/grading/{schedule}/export-leger', [GradingController::class, 'exportLeger'])->name('grading.exportLeger');
});

// ==================== PROCTOR ROUTES ====================
Route::middleware(['auth', 'role:proctor,admin,teacher'])->prefix('proctor')->name('proctor.')->group(function () {
    Route::get('/dashboard', [ProctorController::class, 'index'])->name('dashboard');
    Route::post('/attempt/{attempt}/unlock', [ProctorController::class, 'unlockStudent'])->name('unlockStudent');
    Route::post('/session/{session}/unlock-all', [ProctorController::class, 'unlockAll'])->name('unlockAll');
    Route::post('/student/{student}/reset-login', [ProctorController::class, 'resetStudentLogin'])->name('resetStudentLogin');
    Route::post('/session/{session}/notes', [ProctorController::class, 'saveNotes'])->name('saveNotes');
    Route::get('/session/{session}/print-berita-acara', [ProctorController::class, 'printBeritaAcara'])->name('printBeritaAcara');
});

// ==================== STUDENT ROUTES ====================
Route::middleware(['auth', 'role:student,admin'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentExamController::class, 'dashboard'])->name('dashboard');
    Route::post('/exam/{session}/start', [StudentExamController::class, 'startExam'])->name('exam.start');
    Route::get('/exam/{attempt}', [StudentExamController::class, 'examScreen'])->name('exam.screen');
    Route::post('/exam/{attempt}/submit', [StudentExamController::class, 'submitExam'])->name('exam.submit');
    Route::get('/exam/{attempt}/result', [StudentExamController::class, 'result'])->name('exam.result');
});
