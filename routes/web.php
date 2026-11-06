<?php

use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\ApplicationManagementPreviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyDirectoryController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewPreparationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:6,1')->name('login.authenticate');
Route::post('/demo-login', [AuthController::class, 'demo'])->name('demo.login');
Route::get('/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
Route::post('/invite/{token}', [InvitationController::class, 'store'])->middleware('throttle:6,1')->name('invitation.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/google-calendar/connect', [GoogleCalendarController::class, 'connect'])->name('google-calendar.connect');
    Route::get('/google-calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google-calendar.callback');
    Route::post('/google-calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google-calendar.disconnect');
    Route::post('/invitations', [InvitationController::class, 'create'])->name('invitations.create');

    Route::get('/home', HomeController::class)->name('home.preview');

    Route::get('/applications', ApplicationManagementPreviewController::class)->name('applications.preview');
    Route::post('/applications', [ApplicationManagementPreviewController::class, 'store'])->name('applications.store');
    Route::patch('/applications/{application}', [ApplicationManagementPreviewController::class, 'update'])->name('applications.update');
    Route::delete('/applications/{application}', [ApplicationManagementPreviewController::class, 'destroy'])->name('applications.destroy');
    Route::delete('/applications/status-options/{statusOption}', [ApplicationManagementPreviewController::class, 'destroyStatusOption'])->name('applications.status-options.destroy');

    Route::get('/documents', [ApplicationDocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [ApplicationDocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/parse', [ApplicationDocumentController::class, 'parse'])->name('documents.parse');
    Route::post('/documents', [ApplicationDocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/company/{application}', [ApplicationDocumentController::class, 'showApplication'])->name('documents.application');
    Route::get('/documents/{document}/edit', [ApplicationDocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}', [ApplicationDocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [ApplicationDocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/interviews', [InterviewPreparationController::class, 'index'])->name('interviews.index');
    Route::get('/interviews/company/{application}', [InterviewPreparationController::class, 'show'])->name('interviews.show');
    Route::post('/interviews/company/{application}', [InterviewPreparationController::class, 'save'])->name('interviews.save');
    Route::delete('/interviews/company/{application}', [InterviewPreparationController::class, 'destroy'])->name('interviews.destroy');

    Route::get('/research', [CompanyDirectoryController::class, 'index'])->name('research.index');
    Route::get('/research/company/{application}/edit', [CompanyDirectoryController::class, 'edit'])->name('research.edit');
    Route::put('/research/profile/{profile}', [CompanyDirectoryController::class, 'update'])->name('research.profile.update');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
    Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::get('/schedule/{event}/edit', [ScheduleController::class, 'edit'])->name('schedule.edit');
    Route::put('/schedule/{event}', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::delete('/schedule/{event}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
    Route::patch('/schedule/{event}/completion', [ScheduleController::class, 'toggleCompleted'])->name('schedule.completion');
    Route::post('/schedule/{event}/google-calendar', [GoogleCalendarController::class, 'storeEvent'])->name('schedule.google-calendar.store');
    Route::get('/schedule/{event}/google-calendar/fallback', [ScheduleController::class, 'googleCalendarFallback'])->name('schedule.google-calendar.fallback');
});
