<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\Reports\AttendanceReportController;
use App\Http\Controllers\Admin\Settings\DefaultSmsTemplatesController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupAttachController;
use App\Http\Controllers\GroupAttendanceSettingsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PnlReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReceiptSettingsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SmsReportController;
use App\Http\Controllers\SmsSettingsController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\StaffTelegramBotController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPaymentController;
use App\Http\Controllers\StudentTelegramWebAppController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TextSuggestionController;
use App\Http\Controllers\UserContractController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTelegramController;
use App\Http\Controllers\ValidationSettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//privacy policy
Route::get('privacy-policy', [AdminController::class, 'privacyPolicy'])->name('privacy-policy');

Route::get('/telegram', function () {
    return view('telegram');
})->name('telegram.teacher-app');

//TWA bot routes
Route::get('test', [QuizController::class, 'test']);
Route::get('test/{quiz}/start', [QuizController::class, 'start'])->name('test.start');
Route::post('test/{quiz}/save', [QuizController::class, 'save'])->name('test.save');
Route::get('test/{unique_id}/result', [QuizController::class, 'result'])->name('test.result');

//Teachers TWA bot routes
Route::get('inertia-teachers/login', [StaffTelegramBotController::class, 'loginView'])->name('inertia-teachers.login.view');
Route::post('inertia-teachers/login', [StaffTelegramBotController::class, 'login'])->name('inertia-teachers.login')->middleware('throttle:6,1');
// The mini-app trades Telegram's signed initData for a session here; the group below needs one.
Route::post('inertia-teachers/auth', [StaffTelegramBotController::class, 'authenticate'])->name('inertia-teachers.auth');
Route::group(['prefix' => 'inertia-teachers', 'as' => 'inertia-teachers.', 'middleware' => 'staff.webapp'], function () {
    Route::get('groups/{group}/attendance', [StaffTelegramBotController::class, 'groupAttendanceShow'])->name('groups.attendance');
    Route::post('groups/{group}/attendance', [StaffTelegramBotController::class, 'groupAttendanceStore'])->name('groups.attendance.store');
    Route::get('groups/{group}/attendance/edit', [StaffTelegramBotController::class, 'groupAttendanceEdit'])->name('groups.attendance.edit');
    Route::put('groups/{group}/attendance', [StaffTelegramBotController::class, 'groupAttendanceUpdate'])->name('groups.attendance.update');
    Route::get('groups/{group}', [StaffTelegramBotController::class, 'groupShow'])->name('groups.show');
    Route::get('groups', [StaffTelegramBotController::class, 'groups'])->name('groups');
    Route::get('settings', [StaffTelegramBotController::class, 'settings'])->name('settings');
    Route::get('/', [StaffTelegramBotController::class, 'index'])->name('index');
});

Auth::routes(['register' => false]);

//Language Translation
Route::get('index/{locale}', [HomeController::class, 'lang']);
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('index');

//Lead form
Route::get('lead/{board}', [LeadController::class, 'leadForm'])->name('lead.form');
Route::get('thank-you', [LeadController::class, 'thankYou'])->name('thank-you');
Route::post('lead/{board}', [LeadController::class, 'leadStore'])->name('lead.store');

//google robots
Route::get('/robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, [
        'Content-Type' => 'text/plain',
    ]);
});

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (! session()->has('lang')) {
            session()->put('lang', 'uz');
        }

        return view('index');
    })->middleware('dashboard.redirect')->name('dashboard');

    // change language
    Route::get('lang/{lang}', function ($lang) {
        session()->put('lang', $lang);

        return redirect()->back();
    })->name('lang');

    Route::get('teacher/dashboard', [TeacherController::class, 'index'])->name('teacher.dashboard');

    //Birthdays
    Route::post('birthdays/set-template', [DashboardController::class, 'setTemplate'])->name('birthdays.set-template')->middleware('permission:sms-update');
    Route::post('birthdays/send-sms', [DashboardController::class, 'sendSms'])->name('birthdays.send-sms')->middleware('permission:sms-store');
    Route::get('birthdays', [DashboardController::class, 'birthdays'])->name('birthdays');

    //dashboard calendar
    Route::get('calendar', [DashboardController::class, 'calendar'])->name('calendar.view');
    Route::get('calendar-data', [DashboardController::class, 'getCalendarData'])->name('calendar');

    //wrapped
    Route::get('wrapped', [\App\Http\Controllers\WrappedController::class, 'show'])->name('wrapped.show');

    //courses
    Route::resource('courses', CourseController::class);

    //departments
    Route::resource('departments', DepartmentController::class);

    //roles
    Route::post('roles/delete', [RoleController::class, 'delete'])->name('roles.delete');
    Route::resource('roles', RoleController::class)->except(['destroy']);

    //permissions
    Route::post('permissions/delete', [PermissionController::class, 'delete'])->name('permissions.delete');
    Route::resource('permissions', PermissionController::class)->except(['destroy']);

    //users
    Route::delete('users/delete-penalty/{penalty}', [UserController::class, 'deletePenalty'])->name('users.delete-penalty')->middleware('permission:user-delete-penalty');
    Route::post('users/send-telegram-message/{user}', [UserTelegramController::class, 'sendTelegramMessage'])->name('users.send-telegram-message')->middleware('permission:sms-store');
    Route::post('users/send-telegram-to-multiple', [UserTelegramController::class, 'sendTelegramToMultiple'])->name('users.send-telegram-to-multiple')->middleware('permission:sms-store');
    Route::post('users/add-penalty/{user}', [UserController::class, 'addPenalty'])->name('users.add-penalty')->middleware('permission:user-update');
    Route::get('users/calendar/{user}', [UserController::class, 'getCalendarData'])->name('users.calendar');
    Route::post('users/delete', [UserController::class, 'delete'])->name('users.delete');
    Route::post('users/payment/{user}', [UserController::class, 'payment'])->name('users.payment')->middleware('permission:user-payment-list');
    Route::get('users/payment/{user}', [UserController::class, 'paymentView'])->name('users.payment.view')->middleware('permission:user-payment-list');
    Route::delete('users/{user}/payments/{payment}', [UserController::class, 'deletePayment'])->name('users.payments.delete')->middleware('permission:payment-delete');
    Route::get('users/payments', [UserController::class, 'paymentsList'])->name('users.payments');
    Route::resource('users', UserController::class)->except(['destroy']);

    //user contracts
    Route::put('users/contracts/{user}', [UserContractController::class, 'update'])->name('users.contracts.update')->middleware('permission:user-update');

    //user salaries
    Route::get('salaries/{user}', [UserController::class, 'salaryShow'])->name('users.salaries.show');
    Route::get('salaries', [UserController::class, 'salaries'])->name('users.salaries');

    //groups
    Route::get('groups/agreement/{group}/{student}', [GroupController::class, 'agreementView'])->name('groups.agreement-view');
    Route::post('groups/agreement/{group}/{student}', [GroupController::class, 'agreement'])->name('groups.agreement')->middleware('permission:group-add-student-agreement');
    Route::get('groups/send-sms/{group}', [GroupController::class, 'sendSmsView'])->name('groups.send-sms-view');
    Route::post('groups/complete/{group}', [GroupController::class, 'complete'])->name('groups.complete')->middleware('permission:group-complete');
    Route::get('groups/trial-lesson-notice/{group}', [GroupController::class, 'trialLessonNoticeView'])->name('groups.trial-lesson-notice-view');
    Route::post('groups/trial-lesson-notice/{group}', [GroupController::class, 'trialLessonNotice'])->name('groups.trial-lesson-notice')->middleware('permission:group-trial-lesson-notice');
    Route::get('groups/payment-notice/{group}', [GroupController::class, 'paymentNoticeView'])->name('groups.payment-notice-view');
    Route::post('groups/payment-notice/{group}', [GroupController::class, 'paymentNotice'])->name('groups.payment-notice')->middleware('permission:group-payment-notice');
    Route::get('groups/start-group/{group}', [GroupController::class, 'startGroupView'])->name('groups.start-group-view');
    Route::post('groups/start-group/{group}', [GroupController::class, 'startGroup'])->name('groups.start-group')->middleware('permission:group-update');
    Route::get('groups/debt-settings/{group}', [GroupController::class, 'debtSettingsView'])->name('groups.debt-settings')->middleware('permission:group-update-debt');
    Route::put('groups/update-start-date/{group}', [GroupController::class, 'updateStartDate'])->name('groups.update-start-date')->middleware('permission:group-update');
    Route::put('groups/update-billing-frequency/{group}', [GroupController::class, 'updateBillingFrequency'])->name('groups.update-billing-frequency')->middleware('permission:group-update');
    Route::get('groups/edit-debt/{studentGroupPayment}', [GroupController::class, 'editDebt'])->name('groups.edit-debt')->middleware('permission:group-update-debt');
    Route::put('groups/update-debt/{studentGroupPayment}', [GroupController::class, 'updateDebt'])->name('groups.update-debt')->middleware('permission:group-update-debt');
    Route::patch('groups/mark-no-debt/{studentGroupPayment}', [GroupController::class, 'markAsNoDebt'])->name('groups.mark-no-debt')->middleware('permission:group-update-debt');
    Route::get('groups/add-debt/{group}', [GroupController::class, 'addDebt'])->name('groups.add-debt')->middleware('permission:group-update-debt');
    Route::post('groups/add-debt/{group}', [GroupController::class, 'storeDebt'])->name('groups.store-debt')->middleware('permission:group-update-debt');
    Route::delete('groups/delete-debt/{studentGroupPayment}', [GroupController::class, 'deleteDebt'])->name('groups.delete-debt')->middleware('permission:group-update-debt');
    Route::get('groups/calendar/{group}', [GroupController::class, 'getCalendarData'])->name('groups.calendar');
    Route::get('groups/attach-student', [GroupAttachController::class, 'attachStudentView'])->name('groups.attach-student.view')->middleware('permission:group-attach-student');
    Route::post('groups/detach-with-debt', [GroupAttachController::class, 'detachStudentWithDebt'])->name('groups.detach-with-debt')->middleware('permission:group-detach-student');
    Route::post('groups/attach-student', [GroupAttachController::class, 'attachStudent'])->name('groups.attach')->middleware('permission:group-attach-student');
    Route::post('groups/detach-student', [GroupAttachController::class, 'detachStudent'])->name('groups.detach')->middleware('permission:group-detach-student');
    Route::delete('groups/delete-agreement/{group}', [GroupController::class, 'deleteAgreement'])->name('groups.delete-agreement')->middleware('permission:group-update');
    Route::resource('groups', GroupController::class);

    //groups attendance settings
    Route::put('groups/attendance-settings/{group}', [GroupAttendanceSettingsController::class, 'update'])->name('groups.attendance-settings')->middleware('permission:group-update');

    //students
    Route::post('students/refresh-balance/{student}', [StudentController::class, 'refreshBalance'])->name('students.refresh-balance')->middleware('permission:student-balance');
    Route::get('students/transfer-group/{student}', [StudentController::class, 'transferGroupView'])->name('students.transfer-group-view');
    Route::get('students/import', [StudentController::class, 'importView'])->name('students.import.view');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import')->middleware('permission:student-store');
    Route::post('students/export', [StudentController::class, 'export'])->name('students.export')->middleware('permission:student-index');
    Route::get('students/get-student-group-payment/{studentGroupPayment}', [StudentController::class, 'getStudentGroupPayment'])->name('students.get-student-group-payment');
    Route::post('students/restore/{student}', [StudentController::class, 'restore'])->name('students.restore')->middleware('permission:student-update');
    Route::post('students/payment/{student}', [StudentPaymentController::class, 'store'])->name('students.payment');
    Route::get('students/payment/{payment}/edit', [StudentPaymentController::class, 'edit'])->name('students.payment-edit');
    Route::put('students/payment/{payment}/update', [StudentPaymentController::class, 'update'])->name('students.payment-update');
    Route::delete('students/payment/{payment}/delete', [StudentPaymentController::class, 'delete'])->name('students.payment-delete');
    Route::get('students/payment/{student}', [StudentPaymentController::class, 'create'])->name('students.payment.view');
    Route::get('students/add-debt/{student}', [StudentController::class, 'addDebtView'])->name('students.add-debt.view');
    Route::post('students/add-debt/{student}', [StudentController::class, 'storeDebt'])->name('students.add-debt')->middleware('permission:student-update-debt-amount');
    Route::get('students/get-group-ranges/{group}', [StudentController::class, 'getGroupRanges'])->name('students.get-group-ranges');
    Route::get('students/get-group-info/{group}', [StudentController::class, 'getGroupInfo'])->name('students.get-group-info');
    Route::delete('students/force-delete/{student}', [StudentController::class, 'forceDelete'])->name('students.force-delete')->middleware('permission:student-delete');
    Route::resource('students', StudentController::class);

    //text suggestions
    Route::post('text-suggestions/store', [TextSuggestionController::class, 'store'])->name('text-suggestions.store');
    Route::delete('text-suggestions/{textSuggestion}/delete', [TextSuggestionController::class, 'delete'])->name('text-suggestions.delete');

    //attendance
    Route::get('attendances/{attendance}', [\App\Http\Controllers\Admin\AttendanceRecordController::class, 'show'])->name('attendances.show');
    Route::get('attendance/{group}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit')->middleware('permission:group-attendance-edit');
    Route::put('attendance/{group}/update', [AttendanceController::class, 'update'])->name('attendance.update')->middleware('permission:group-attendance-edit');
    Route::get('attendance/{group}', [AttendanceController::class, 'show'])->name('attendance')->middleware('permission:group-attendance');
    Route::post('attendance/{group}', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:group-attendance');

    //expenses
    Route::delete('expenses/settings/delete-place/{id}', [ExpenseController::class, 'deleteSettingsPlace'])->name('expenses.settings.delete-place')->middleware('permission:expense-delete');
    Route::delete('expenses/settings/delete-item/{id}', [ExpenseController::class, 'deleteSettingsItem'])->name('expenses.settings.delete-item')->middleware('permission:expense-delete');
    Route::get('expenses/settings/edit-place/{id}', [ExpenseController::class, 'editSettingsPlace'])->name('expenses.settings.edit-place');
    Route::put('expenses/settings/edit-place/{id}', [ExpenseController::class, 'updateSettingsPlace'])->name('expenses.settings.update-place')->middleware('permission:expense-update');
    Route::get('expenses/settings/create-place', [ExpenseController::class, 'createSettingsPlace'])->name('expenses.settings.create-place');
    Route::post('expenses/settings/create-place', [ExpenseController::class, 'storeSettingsPlace'])->name('expenses.settings.store-place')->middleware('permission:expense-store');
    Route::get('expenses/settings/edit-item/{id}', [ExpenseController::class, 'editSettingsItem'])->name('expenses.settings.edit-item');
    Route::put('expenses/settings/edit-item/{id}', [ExpenseController::class, 'updateSettingsItem'])->name('expenses.settings.update-item')->middleware('permission:expense-update');
    Route::get('expenses/settings', [ExpenseController::class, 'indexSettings'])->name('expenses.settings.index');
    Route::get('expenses/settings/create-item', [ExpenseController::class, 'createSettingsItem'])->name('expenses.settings.create-item');
    Route::post('expenses/settings/create-item', [ExpenseController::class, 'storeSettingsItem'])->name('expenses.settings.store-item')->middleware('permission:expense-store');
    Route::resource('expenses', ExpenseController::class);

    //discounts
    Route::get('/payments/discounts', [PaymentController::class, 'discount'])->name('payments.discounts');

    //payments
    Route::post('payments/{payments}/send-sms', [PaymentController::class, 'sendSms'])->name('payments.send-sms');
    Route::get('payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('payments/{payment}/update', [PaymentController::class, 'update'])->name('payments.update');
    Route::get('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::post('payments/{payment}/refund', [PaymentController::class, 'refundStore'])->name('payments.refund-store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    //Profile Settings
    Route::get('profile-settings', [UserController::class, 'profileSettings'])->name('profile.settings');
    Route::post('change-password', [UserController::class, 'changePassword'])->name('change.password');
    Route::post('change-profile', [UserController::class, 'changeProfile'])->name('change.profile');

    //boards
    Route::post('leads/set-url/{board}', [LeadController::class, 'setUrl'])->name('boards.set-url')->middleware('permission:lead-index');
    Route::get('leads/board/{board}', [LeadController::class, 'boardShow'])->name('boards.show');
    Route::get('leads/transfer-to-group/{board}', [LeadController::class, 'transferToGroupView'])->name('boards.transfer-to-group-view');
    Route::post('leads/transfer-to-group/{board}', [LeadController::class, 'transferToGroup'])->name('boards.transfer-to-group')->middleware('permission:lead-index');

    //leads
    Route::get('leads/{lead}/reminders', [LeadController::class, 'getReminders'])->name('leads.reminders.get');
    Route::get('leads/user-reminders', [LeadController::class, 'getUserReminders'])->name('leads.user-reminders');
    Route::put('leads/{reminder}/update-reminder', [LeadController::class, 'updateReminder'])->name('leads.update-reminder')->middleware('permission:lead-index');
    Route::post('leads/{lead}/add-reminder', [LeadController::class, 'addReminder'])->name('leads.add-reminder')->middleware('permission:lead-index');
    Route::delete('leads/reminders/{reminder}', [LeadController::class, 'deleteReminder'])->name('leads.delete-reminder')->middleware('permission:lead-index');
    Route::get('leads/calls', [LeadController::class, 'callsIndex'])->name('leads.calls-index');
    Route::post('leads/store-call/{lead}', [LeadController::class, 'storeCall'])->name('leads.store-call')->middleware('permission:lead-index');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::get('leads/import', [LeadController::class, 'import'])->name('leads.import');
    Route::post('leads/import', [LeadController::class, 'importStore'])->name('leads.import-store')->middleware('permission:lead-index');
    Route::put('leads/update-lead/{lead}', [LeadController::class, 'updateLead'])->name('leads.update-lead')->middleware('permission:lead-index');
    Route::post('leads/store-lead', [LeadController::class, 'storeLead'])->name('leads.store-lead')->middleware('permission:lead-index');
    Route::post('leads/store', [LeadController::class, 'storeStudent'])->name('leads.store-student')->middleware('permission:lead-index');
    Route::post('leads/convert-to-student', [LeadController::class, 'convertToStudent'])->name('leads.convert-to-student')->middleware('permission:lead-index');
    Route::post('leads/add-comment', [LeadController::class, 'addComment'])->name('leads.add-comment')->middleware('permission:lead-index');
    Route::get('leads-table', [LeadController::class, 'leadsTable'])->name('leads.table');
    Route::get('leads/table-page', [LeadController::class, 'tablePage'])->name('leads.table-page');
    Route::get('leads/table-data', [LeadController::class, 'getTableData'])->name('leads.table-data');
    Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::post('leads/prepare-sms-selection', [LeadController::class, 'prepareSmsSelection'])->name('leads.prepare-sms-selection')->middleware('permission:lead-index');
    Route::post('leads/{lead}/toggle-attend', [LeadController::class, 'toggleAttend'])->name('leads.toggle-attend')->middleware('permission:lead-index');
    Route::get('leads/board', [LeadController::class, 'leadsBoard'])->name('leads.board');
    Route::get('leads/reminders', [LeadController::class, 'reminders'])->name('leads.reminders');
    Route::get('leads/my-reminders', [LeadController::class, 'remindersPage'])->name('leads.my-reminders');
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/settings', [LeadController::class, 'settings'])->name('leads.settings');
    Route::get('leads/inertia', [LeadController::class, 'inertiaIndex'])->name('leads.inertia');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store')->middleware('permission:lead-index');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update')->middleware('permission:lead-index');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy')->middleware('permission:lead-index');
    Route::post('leads/{lead}/send-telegram-to-teacher', [LeadController::class, 'sendTelegramToTeacher'])->name('leads.send-telegram-to-teacher')->middleware('permission:lead-index');
    Route::get('leads/{lead}/details', [LeadController::class, 'getLeadDetails'])->name('leads.details');
    Route::get('leads/search', [LeadController::class, 'searchLeads'])->name('leads.search');
    Route::post('leads/update-order', [LeadController::class, 'updateLeadOrder'])->name('leads.update-order')->middleware('permission:lead-index');
    Route::get('leads/load-more', [LeadController::class, 'loadMoreLeads'])->name('leads.load-more');

    // Archived leads routes
    Route::get('leads/archived-page', [LeadController::class, 'archivedIndex'])->name('leads.archived-page');
    Route::get('leads/archived', [LeadController::class, 'getArchivedLeads'])->name('leads.archived');
    Route::post('leads/{lead}/restore', [LeadController::class, 'restoreLead'])->name('leads.restore')->middleware('permission:lead-index');
    Route::delete('leads/{lead}/force-delete', [LeadController::class, 'forceDeleteLead'])->name('leads.force-delete')->middleware('permission:lead-index');

    // Comments routes
    Route::get('leads/{lead}/comments', [LeadController::class, 'getComments'])->name('leads.comments');
    Route::post('leads/{lead}/comments', [LeadController::class, 'storeComment'])->name('leads.comments.store')->middleware('permission:lead-index');
    Route::delete('leads/{lead}/comments/{comment}', [LeadController::class, 'deleteComment'])->name('leads.comments.delete')->middleware('permission:lead-index');

    // Calls routes
    Route::get('leads/{lead}/calls', [LeadController::class, 'getCalls'])->name('leads.calls');
    Route::post('leads/{lead}/calls', [LeadController::class, 'storeLeadCall'])->name('leads.calls.store')->middleware('permission:lead-index');
    Route::delete('leads/{lead}/calls/{call}', [LeadController::class, 'deleteCall'])->name('leads.calls.delete')->middleware('permission:lead-index');

    // Activities routes
    Route::get('leads/{lead}/activities', [LeadController::class, 'getActivities'])->name('leads.activities');

    // Lead Collections API routes
    Route::get('lead-collections', [LeadController::class, 'getLeadCollections'])->name('lead-collections.index');
    Route::post('lead-collections', [LeadController::class, 'storeLeadCollection'])->name('lead-collections.store')->middleware('permission:lead-index');
    Route::put('lead-collections/{id}', [LeadController::class, 'updateLeadCollection'])->name('lead-collections.update')->middleware('permission:lead-index');
    Route::delete('lead-collections/{id}', [LeadController::class, 'deleteLeadCollection'])->name('lead-collections.delete')->middleware('permission:lead-index');
    Route::post('lead-collections/update-order', [LeadController::class, 'updateLeadCollectionOrder'])->name('lead-collections.update-order')->middleware('permission:lead-index');
    Route::post('lead-collections/{id}/generate-link', [LeadController::class, 'generateLeadCollectionLink'])->name('lead-collections.generate-link')->middleware('permission:lead-index');
    Route::get('lead-collections/{id}/get-link', [LeadController::class, 'getLeadCollectionLink'])->name('lead-collections.get-link');
    Route::delete('lead-collections/{id}/remove-link', [LeadController::class, 'removeLeadCollectionLink'])->name('lead-collections.remove-link')->middleware('permission:lead-index');

    // Lead Boards API routes
    Route::get('lead-boards', [LeadController::class, 'getLeadBoards'])->name('lead-boards.index');
    Route::post('lead-boards', [LeadController::class, 'storeLeadBoard'])->name('lead-boards.store')->middleware('permission:lead-index');
    Route::put('lead-boards/{id}', [LeadController::class, 'updateLeadBoard'])->name('lead-boards.update')->middleware('permission:lead-index');
    Route::delete('lead-boards/{id}', [LeadController::class, 'deleteLeadBoard'])->name('lead-boards.delete')->middleware('permission:lead-index');
    Route::post('lead-boards/{id}/generate-url', [LeadController::class, 'generateLeadBoardUrl'])->name('lead-boards.generate-url')->middleware('permission:lead-index');
    Route::delete('lead-boards/{id}/remove-url', [LeadController::class, 'removeLeadBoardUrl'])->name('lead-boards.remove-url')->middleware('permission:lead-index');

    //lead collection
    Route::get('leads/settings/create-collection', [LeadController::class, 'createCollection'])->name('leads.settings.create-collection');
    Route::post('leads/settings/create-collection', [LeadController::class, 'storeCollection'])->name('leads.settings.store-collection')->middleware('permission:lead-index');
    Route::get('leads/settings/edit-collection/{collection}', [LeadController::class, 'editCollection'])->name('leads.settings.edit-collection');
    Route::put('leads/settings/edit-collection/{collection}', [LeadController::class, 'updateCollection'])->name('leads.settings.update-collection')->middleware('permission:lead-index');
    Route::delete('leads/settings/delete-collection/{collection}', [LeadController::class, 'deleteCollection'])->name('leads.settings.delete-collection')->middleware('permission:lead-index');

    //lead board
    Route::get('leads/settings/create-board', [LeadController::class, 'createBoard'])->name('leads.settings.create-board');
    Route::post('leads/settings/create-board', [LeadController::class, 'storeBoard'])->name('leads.settings.store-board')->middleware('permission:lead-index');
    Route::get('leads/settings/edit-board/{board}', [LeadController::class, 'editBoard'])->name('leads.settings.edit-board');
    Route::put('leads/settings/edit-board/{board}', [LeadController::class, 'updateBoard'])->name('leads.settings.update-board')->middleware('permission:lead-index');
    Route::delete('leads/settings/delete-board/{board}', [LeadController::class, 'deleteBoard'])->name('leads.settings.delete-board')->middleware('permission:lead-index');

    //accounts
    Route::get('accounts/transfer', [AccountController::class, 'transfer'])->name('accounts.transfer')->middleware('permission:account-update');
    Route::post('accounts/transfer', [AccountController::class, 'transferStore'])->name('accounts.transfer-store')->middleware('permission:account-update');
    Route::get('accounts/transaction/{account}', [AccountController::class, 'transactionView'])->name('accounts.transaction-view')->middleware('permission:account-update');
    Route::post('accounts/transaction/{account}', [AccountController::class, 'transaction'])->name('accounts.transaction')->middleware('permission:account-update');
    Route::resource('accounts', AccountController::class);

    //reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/educational', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'index'])->name('educational');
        Route::get('/educational/cards', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'cards'])->name('educational.cards');
        Route::get('/educational/charts', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'charts'])->name('educational.charts');
        Route::get('/educational/telegram-stats', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'telegramStats'])->name('educational.telegram-stats');
        Route::get('/educational/telegram-group-students', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'telegramGroupStudents'])->name('educational.telegram-group-students');
        Route::get('/educational/user-calls-report', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'userCallsReport'])->name('educational.user-calls-report');
        Route::get('/educational/group-attendance', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'groupAttendance'])->name('educational.group-attendance');
        Route::get('/educational/homework-report', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'homeworkReport'])->name('educational.homework-report');
        Route::get('/educational/attendance-detail', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'attendanceDetail'])->name('educational.attendance-detail');
        Route::get('/educational/export-attendance', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'exportAttendance'])->name('educational.export-attendance');
        Route::get('/educational/export-students', [\App\Http\Controllers\Admin\Reports\EducationalReportController::class, 'exportStudents'])->name('educational.export-students');
        Route::get('/leads', [ReportController::class, 'leads'])->name('leads');
        Route::get('/debts', [ReportController::class, 'debts'])->name('debts');
        Route::get('/sms', [SmsReportController::class, 'index'])->name('sms');
        Route::get('/attendance', [AttendanceReportController::class, 'index'])->name('attendance');
        Route::get('/pnl', [PnlReportController::class, 'index'])->name('pnl');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::post('/process-group-payments', [ReportController::class, 'processGroupPayments'])->name('process-group-payments');
    });

    //settings
    Route::put('settings/menu-settings', [SettingsController::class, 'menuSettingsUpdate'])->name('settings.menu-settings-update')->middleware('permission:settings-index');
    Route::get('settings/menu-settings', [SettingsController::class, 'menuSettingsView'])->name('settings.menu-settings-view')->middleware('permission:settings-index');
    Route::put('settings/groups/attendance-update', [SettingsController::class, 'groupAttendanceUpdate'])->name('settings.group-attendance-update')->middleware('permission:settings-index');
    Route::get('settings/groups', [SettingsController::class, 'groupSettings'])->name('settings.group-settings')->middleware('permission:settings-index');
    Route::put('settings/set-template', [SettingsController::class, 'setTemplate'])->name('settings.set-template')->middleware('permission:settings-index');
    Route::put('settings/set-payment-template', [SettingsController::class, 'setPaymentTemplate'])->name('settings.set-payment-template')->middleware('permission:settings-index');
    Route::put('settings/set-debt-template', [SmsSettingsController::class, 'setDebtTemplate'])->name('settings.set-debt-template')->middleware('permission:settings-index');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('permission:settings-index');
    Route::get('settings/general', [SettingsController::class, 'generalSettingsIndex'])->name('settings.general-settings-index')->middleware('permission:settings-index');
    Route::post('settings/update-logo', [SettingsController::class, 'updateLogo'])->name('settings.update-logo')->middleware('permission:settings-index');
    Route::put('settings/update', [SettingsController::class, 'generalSettingsUpdate'])->name('settings.general-settings-update')->middleware('permission:settings-index');
    Route::get('settings/payment-types', [SettingsController::class, 'paymentTypes'])->name('settings.payment-types')->middleware('permission:settings-index');
    Route::get('settings/payment-types/reorder', [SettingsController::class, 'paymentTypesReorder'])->name('settings.payment-types.reorder')->middleware('permission:settings-index');
    Route::post('settings/payment-types/reorder', [SettingsController::class, 'updatePaymentTypesReorder'])->name('settings.payment-types.update-order')->middleware('permission:settings-index');
    Route::get('settings/payment-types/create', [SettingsController::class, 'createPaymentType'])->name('settings.payment-types.create')->middleware('permission:settings-index');
    Route::post('settings/payment-types', [SettingsController::class, 'storePaymentType'])->name('settings.payment-types.store')->middleware('permission:settings-index');
    Route::get('settings/payment-types/edit/{paymentType}', [SettingsController::class, 'editPaymentType'])->name('settings.payment-types.edit')->middleware('permission:settings-index');
    Route::put('settings/payment-types/{paymentType}/update', [SettingsController::class, 'updatePaymentType'])->name('settings.payment-types.update')->middleware('permission:settings-index');
    Route::delete('settings/payment-types/{paymentType}/delete', [SettingsController::class, 'deletePaymentType'])->name('settings.payment-types.delete')->middleware('permission:settings-index');
    Route::resource('settings/rooms', RoomController::class)->middleware('permission:settings-index');
    Route::get('settings/sms/default-templates', [DefaultSmsTemplatesController::class, 'index'])->name('settings.sms.default-templates');
    Route::get('settings/sms/create', [SmsTemplateController::class, 'create'])->name('settings.sms.create')->middleware('permission:settings-index');
    Route::get('settings/sms/{id}', [SmsTemplateController::class, 'show'])->name('settings.sms.show')->middleware('permission:settings-index');
    Route::get('settings/sms', [SettingsController::class, 'sms'])->name('settings.sms')->middleware('permission:settings-index');
    Route::delete('settings/sms/{id}/destroy', [SmsTemplateController::class, 'destroy'])->name('settings.sms.destroy')->middleware('permission:settings-index');
    Route::put('settings/sms/{id}/update', [SmsTemplateController::class, 'update'])->name('settings.sms.update')->middleware('permission:settings-index');
    Route::get('settings/sms/{id}/edit', [SmsTemplateController::class, 'edit'])->name('settings.sms.edit')->middleware('permission:settings-index');
    Route::post('settings/sms/store', [SmsTemplateController::class, 'store'])->name('settings.sms.store')->middleware('permission:settings-index');
    Route::post('settings/sms/{id}/check-status', [SmsTemplateController::class, 'checkStatus'])->name('settings.sms.check-status')->middleware('permission:settings-index');
    Route::get('settings/leads', [SettingsController::class, 'leads'])->name('settings.leads')->middleware('permission:settings-index');
    Route::put('settings/leads/update', [SettingsController::class, 'leadsUpdate'])->name('settings.leads.update')->middleware('permission:settings-index');
    Route::get('settings/telegram', [SettingsController::class, 'telegram'])->name('settings.telegram')->middleware('permission:settings-index');
    Route::post('settings/telegram/update', [SettingsController::class, 'setTelegram'])->name('settings.telegram.set-token')->middleware('permission:settings-index');
    Route::get('settings/staff', [SettingsController::class, 'staffSettingsView'])->name('settings.staff-settings-view')->middleware('permission:settings-index');
    Route::put('settings/staff/bot-token', [SettingsController::class, 'updateStaffBotToken'])->name('settings.staff.update-bot-token')->middleware('permission:settings-index');
    Route::post('settings/teachers-telegram-bot', [SettingsController::class, 'telegramBot'])->name('settings.teachers-bot')->middleware('permission:settings-index');
    Route::get('settings/receipts', [ReceiptSettingsController::class, 'index'])->name('settings.receipts.index')->middleware('permission:settings-index');
    Route::get('settings/students-telegram-webapp', [StudentTelegramWebAppController::class, 'index'])->name('settings.students-telegram-webapp')->middleware('permission:settings-index');
    Route::put('settings/students-telegram-webapp', [StudentTelegramWebAppController::class, 'update'])->name('settings.students-telegram-webapp.update')->middleware('permission:settings-index');

    //branches
    Route::post('branches/change-current', [BranchController::class, 'changeCurrent'])->name('branches.change-current');
    Route::post('branches/delete', [BranchController::class, 'delete'])->name('branches.delete');
    Route::resource('branches', BranchController::class);

    //sms templates
    Route::resource('sms-templates', SmsTemplateController::class)->names([
        'index' => 'admin.sms-templates.index',
        'create' => 'admin.sms-templates.create',
        'store' => 'admin.sms-templates.store',
        'show' => 'admin.sms-templates.show',
        'edit' => 'admin.sms-templates.edit',
        'update' => 'admin.sms-templates.update',
        'destroy' => 'admin.sms-templates.destroy',
    ])->middleware('permission:sms-create');

    //sms template actions
    Route::post('sms-templates/{sms_template}/send-to-moderation', [SmsTemplateController::class, 'sendToModeration'])->name('admin.sms-templates.send-to-moderation')->middleware('permission:sms-create');
    Route::get('sms-templates/statistics', [SmsTemplateController::class, 'statistics'])->name('admin.sms-templates.statistics')->middleware('permission:sms-create');

    //sms
    Route::delete('sms/delete', [SmsController::class, 'delete'])->name('sms.delete')->middleware('permission:sms-delete');
    Route::post('sms/return-to-pending', [SmsController::class, 'returnToPending'])->name('sms.return-to-pending')->middleware('permission:sms-store');
    Route::get('sms/scheduled', [SmsController::class, 'scheduled'])->name('sms.scheduled')->middleware('permission:sms-index');
    Route::post('sms//send-scheduled', [SmsController::class, 'sendScheduled'])->name('sms.send-scheduled')->middleware('permission:sms-store');
    Route::post('sms/{sms}/send-single', [SmsController::class, 'sendSingle'])->name('sms.send-single')->middleware('permission:sms-store');
    Route::post('sms/send-smart', [SmsController::class, 'sendSmart'])->name('sms.send-smart')->middleware('permission:sms-store');
    Route::post('sms/send', [SmsController::class, 'send'])->name('sms.send')->middleware('permission:sms-store');
    Route::post('sms/send-to-pending', [SmsController::class, 'sendToPending'])->name('sms.send-to-pending')->middleware('permission:sms-store');
    Route::get('sms/create', [SmsController::class, 'create'])->name('sms.create')->middleware('permission:sms-create');
    Route::get('sms/batches', [SmsController::class, 'batches'])->name('sms.batches')->middleware('permission:sms-index');
    Route::get('sms/{sms}', [SmsController::class, 'show'])->name('sms.show')->middleware('permission:sms-show');
    Route::get('sms', [SmsController::class, 'index'])->name('sms.index')->middleware('permission:sms-index');

    //dark mode
    Route::get('dark-mode', [SettingsController::class, 'darkMode'])->name('dark-mode');

    //validation settings
    Route::put('validation-settings/update', [ValidationSettingsController::class, 'update'])->name('validation-settings.update')->middleware('permission:settings-index');

    //telegrams
    Route::get('telegram/messages', [TelegramController::class, 'messagesIndex'])->name('telegram.messages.index')->middleware('permission:sms-create');
    Route::get('telegram/send', [TelegramController::class, 'sendMessage'])->name('telegram.send')->middleware('permission:sms-create');
    Route::post('telegrams/send', [TelegramController::class, 'send'])->name('telegrams.send')->middleware('permission:sms-store');
    Route::post('telegrams/set-daily-report', [TelegramController::class, 'setDailyReport'])->name('telegrams.set-daily-report')->middleware('permission:settings-index');
    Route::post('telegrams/{telegram}/delete-daily-report-item', [TelegramController::class, 'deleteDailyReportItem'])->name('telegrams.delete-daily-report-item')->middleware('permission:settings-index');
    Route::resource('telegrams', TelegramController::class)->except(['index', 'show', 'edit', 'update'])->middleware('permission:sms-create');

    //exams
    Route::get('exams/{exam}/create-student-exams', [ExamController::class, 'createStudentExams'])->name('exams.create-student-exams');
    Route::post('exams/{exam}/store-student-exams', [ExamController::class, 'storeStudentExams'])->name('exams.store-student-exams');
    Route::delete('exams/{exam}/delete-student-exams', [ExamController::class, 'deleteStudentExams'])->name('exams.delete-student-exams');
    Route::resource('exams', ExamController::class);

    //quizzes
    Route::get('quizzes/{quiz}/create-test', [TestController::class, 'create'])->name('quizzes.create-test');
    Route::post('quizzes/{quiz}/create-test', [TestController::class, 'store'])->name('quizzes.store-test');
    Route::post('quizzes/set-token', [QuizController::class, 'setToken'])->name('quizzes.set-token')->middleware('permission:settings-index');
    Route::resource('quizzes', QuizController::class);

    //tests
    Route::delete('tests/{test}', [TestController::class, 'destroy'])->name('quizzes.destroy-test');

    //clear cache
    Route::post('clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache')->middleware('permission:settings-index');

    //products
    Route::resource('products', ProductController::class);

});

// TWA Routes
Route::name('twa.')->group(function () {
    // The bot's "open app" button lands here; the id in the path is only a claim.
    Route::get('/twa/login/{telegram_id}', [StudentTelegramWebAppController::class, 'login'])->name('login');
    // The mini-app trades Telegram's signed initData for a session here; the group below needs one.
    Route::post('/twa/auth', [StudentTelegramWebAppController::class, 'authenticate'])->name('auth');

    Route::middleware('student.webapp')->group(function () {
        Route::get('/twa/dashboard', [StudentTelegramWebAppController::class, 'dashboard'])->name('dashboard');
        Route::get('/twa/attendance', [StudentTelegramWebAppController::class, 'attendance'])->name('attendance');
        Route::get('/twa/settings', [StudentTelegramWebAppController::class, 'settings'])->name('settings');
        Route::get('/twa/payments', [StudentTelegramWebAppController::class, 'payments'])->name('payments');
        Route::patch('/twa/settings/profile', [StudentTelegramWebAppController::class, 'updateProfile'])->name('settings.profile');
    });
});
