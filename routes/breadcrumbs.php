<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

//courses
Breadcrumbs::for('admin.courses.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.courses.courses'));
});

Breadcrumbs::for('admin.courses.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.courses.courses'), route('admin.courses.index'));
    $trail->push(__('translation.courses.add'));
});

Breadcrumbs::for('admin.courses.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.courses.courses'), route('admin.courses.index'));
    $trail->push(__('translation.courses.show'));
});

Breadcrumbs::for('admin.courses.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.courses.courses'), route('admin.courses.index'));
    $trail->push(__('translation.courses.edit'));
});

//departments
Breadcrumbs::for('admin.departments.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.departments.departments'));
});

Breadcrumbs::for('admin.departments.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.departments.departments'), route('admin.departments.index'));
    $trail->push(__('translation.departments.add'));
});

Breadcrumbs::for('admin.departments.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.departments.departments'), route('admin.departments.index'));
    $trail->push(__('translation.departments.show'));
});

Breadcrumbs::for('admin.departments.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.departments.departments'), route('admin.departments.index'));
    $trail->push(__('translation.departments.edit'));
});

//roles
Breadcrumbs::for('admin.roles.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.roles.roles'));
});

Breadcrumbs::for('admin.roles.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.roles.roles'), route('admin.roles.index'));
    $trail->push(__('translation.roles.add'));
});

Breadcrumbs::for('admin.roles.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.roles.roles'), route('admin.roles.index'));
    $trail->push(__('translation.roles.show'));
});

Breadcrumbs::for('admin.roles.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.roles.roles'), route('admin.roles.index'));
    $trail->push(__('translation.roles.edit'));
});

//permissions
Breadcrumbs::for('admin.permissions.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.permissions.permissions'));
});

Breadcrumbs::for('admin.permissions.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.permissions.permissions'), route('admin.permissions.index'));
    $trail->push(__('translation.permissions.add'));
});

Breadcrumbs::for('admin.permissions.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.permissions.permissions'), route('admin.permissions.index'));
    $trail->push(__('translation.permissions.show'));
});

Breadcrumbs::for('admin.permissions.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.permissions.permissions'), route('admin.permissions.index'));
    $trail->push(__('translation.permissions.edit'));
});

//users
Breadcrumbs::for('admin.users.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.staff'));
});

Breadcrumbs::for('admin.users.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.staff'), route('admin.users.index'));
    $trail->push(__('translation.users.add'));
});

Breadcrumbs::for('admin.users.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.staff'), route('admin.users.index'));
    $trail->push(__('translation.users.show'));
});

Breadcrumbs::for('admin.users.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.staff'), route('admin.users.index'));
    $trail->push(__('translation.users.edit'));
});

Breadcrumbs::for('admin.users.payment.view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.staff'), route('admin.users.index'));
    $trail->push(__('translation.users.payment'));
});

Breadcrumbs::for('admin.users.payments', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payments.payments'));
});

Breadcrumbs::for('admin.users.salaries', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.salaries'));
});

Breadcrumbs::for('admin.users.salaries.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.users.salaries'), route('admin.users.salaries'));
    $trail->push(__('translation.users.salary'));
});

//groups
Breadcrumbs::for('admin.groups.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'));
});

Breadcrumbs::for('admin.groups.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.add'));
});

Breadcrumbs::for('admin.groups.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.show'));
});

Breadcrumbs::for('admin.groups.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.settings'));
});

Breadcrumbs::for('admin.groups.attach-student.view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.attach'));
});

Breadcrumbs::for('admin.groups.add-debt', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.add_debt'));
});

Breadcrumbs::for('admin.groups.start-group-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.start_lessons'));
});

Breadcrumbs::for('admin.groups.debt-settings', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.debt_settings'));
});

Breadcrumbs::for('admin.groups.payment-notice-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.students.payment_notice'));
});

Breadcrumbs::for('admin.groups.trial-lesson-notice-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.students.trial_lesson_notice'));
});

Breadcrumbs::for('admin.groups.edit-debt', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.edit-debt'));
});

Breadcrumbs::for('admin.groups.agreement-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.groups'), route('admin.groups.index'));
    $trail->push(__('translation.groups.agreement'));
});

//students
Breadcrumbs::for('admin.students.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'));
});

Breadcrumbs::for('admin.students.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.add'));
});

Breadcrumbs::for('admin.students.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.show'));
});

Breadcrumbs::for('admin.students.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.edit'));
});

Breadcrumbs::for('admin.students.import.view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.import'));
});

Breadcrumbs::for('admin.students.transfer-group-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.transfer_group'));
});

//attendance
Breadcrumbs::for('admin.attendance', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.groups.attendance'));
});

Breadcrumbs::for('admin.attendance.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.btn.edit_attendance'));
});

//payment
Breadcrumbs::for('admin.students.payment.view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.payment'), route('admin.students.index'));
    $trail->push(__('translation.students.payment'));
});

Breadcrumbs::for('admin.students.payment-edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.payment_edit'));
});

Breadcrumbs::for('admin.students.add-debt.view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.students.add_debt'));
});

//Expenses
Breadcrumbs::for('admin.expenses.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.expenses'), route('admin.expenses.index'));
});

Breadcrumbs::for('admin.expenses.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.expenses'), route('admin.expenses.index'));
    $trail->push(__('translation.expenses.expense'));
});

Breadcrumbs::for('admin.expenses.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.expenses'), route('admin.expenses.index'));
    $trail->push(__('translation.expenses.add'));
});

Breadcrumbs::for('admin.expenses.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.expenses'), route('admin.expenses.index'));
    $trail->push(__('translation.expenses.edit'));
});

//Expense Settings
Breadcrumbs::for('admin.expenses.settings.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.expenses'), route('admin.expenses.index'));
    $trail->push(__('translation.expenses.settings'), route('admin.expenses.settings.index'));
});

Breadcrumbs::for('admin.expenses.settings.create-item', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.settings'), route('admin.expenses.settings.index'));
    $trail->push(__('translation.expenses.add_item'));
});

Breadcrumbs::for('admin.expenses.settings.edit-item', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.settings'), route('admin.expenses.settings.index'));
    $trail->push(__('translation.expenses.edit_item'));
});

Breadcrumbs::for('admin.expenses.settings.edit-place', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.settings'), route('admin.expenses.settings.index'));
    $trail->push(__('translation.expenses.edit_place'));
});

Breadcrumbs::for('admin.expenses.settings.create-place', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.expenses.settings'), route('admin.expenses.settings.index'));
    $trail->push(__('translation.expenses.add_place'));
});

//payments
Breadcrumbs::for('admin.payments.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payments.payments'), route('admin.payments.index'));
});

Breadcrumbs::for('admin.payments.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payments.payments'), route('admin.payments.index'));
    $trail->push(__('translation.payments.payment'));
});

Breadcrumbs::for('admin.payments.refund', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payments.payments'), route('admin.payments.index'));
    $trail->push(__('translation.payments.refund'));
});

Breadcrumbs::for('admin.payments.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payments.payments'), route('admin.payments.index'));
    $trail->push(__('translation.payments.edit'));
});

//discounts
Breadcrumbs::for('admin.payments.discounts', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payments.payments'), route('admin.payments.index'));
    $trail->push(__('translation.payments.discounts'));
});

//profile settings
Breadcrumbs::for('admin.profile.settings', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.profile.settings'), route('admin.profile.settings'));
});

//leads
Breadcrumbs::for('admin.leads.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
});

Breadcrumbs::for('admin.leads.reminders', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.reminders'));
});

Breadcrumbs::for('admin.leads.my-reminders', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.my_reminders'));
});

Breadcrumbs::for('admin.leads.board', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
});

Breadcrumbs::for('admin.leads.table', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.table'));
});

Breadcrumbs::for('admin.leads.table-page', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.table'));
});

Breadcrumbs::for('admin.leads.table-data', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.table'));
});

Breadcrumbs::for('admin.leads.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.create_lead'));
});

Breadcrumbs::for('admin.leads.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.show'));
});

Breadcrumbs::for('admin.leads.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.edit_lead'));
});

Breadcrumbs::for('admin.leads.import', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.import_from_excel'));
});

Breadcrumbs::for('admin.boards.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.board'));
});

Breadcrumbs::for('admin.leads.calls-index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.calls'));
});

//leads collection settings
Breadcrumbs::for('admin.leads.settings', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.settings'));
});

Breadcrumbs::for('admin.leads.settings.create-collection', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.settings'), route('admin.leads.settings'));
    $trail->push(__('translation.leads.add_collection'));
});

Breadcrumbs::for('admin.leads.settings.edit-collection', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.settings'), route('admin.leads.settings'));
    $trail->push(__('translation.leads.edit_collection'));
});

Breadcrumbs::for('admin.boards.transfer-to-group-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.leads'), route('admin.leads.index'));
    $trail->push(__('translation.leads.transfer_leads'));
});

//lead board settings
Breadcrumbs::for('admin.leads.settings.create-board', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.settings'), route('admin.leads.settings'));
    $trail->push(__('translation.leads.add_board'));
});

Breadcrumbs::for('admin.leads.settings.edit-board', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.leads.settings'), route('admin.leads.settings'));
    $trail->push(__('translation.leads.edit_board'));
});

Breadcrumbs::for('admin.settings.leads', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.leads.settings'));
});

//telegram settings
Breadcrumbs::for('admin.settings.telegram', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.telegram_integration'));
});

Breadcrumbs::for('admin.telegrams.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.telegram_integration'), route('admin.settings.telegram'));
    $trail->push(__('translation.telegram.add'));
});

Breadcrumbs::for('admin.telegram.send', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.students.students'), route('admin.students.index'));
    $trail->push(__('translation.telegram.send_message'));
});

Breadcrumbs::for('admin.telegram.messages.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.telegram.messages'));
});

// Student Telegram Web App Settings
Breadcrumbs::for('admin.settings.students-telegram-webapp', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.telegram.student_twa_settings'));
});

//calendar
Breadcrumbs::for('admin.calendar.view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.dashboard'), route('admin.dashboard'));
    $trail->push(__('translation.calendar'));
});

//wrapped
Breadcrumbs::for('admin.wrapped.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.dashboard'), route('admin.dashboard'));
    $trail->push('Wrapped');
});

//reports
Breadcrumbs::for('admin.reports.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'));
});

Breadcrumbs::for('admin.reports.sms', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.reports.sms'));
});

Breadcrumbs::for('admin.reports.educational', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.reports.education'));
});

Breadcrumbs::for('admin.reports.educational.attendance-detail', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.reports.education'), route('admin.reports.educational'));
    $trail->push(__('translation.reports.attendance_detail'));
});

Breadcrumbs::for('admin.attendances.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.dashboard'), route('admin.dashboard'));
    $trail->push(__('translation.attendance_record.page_title'));
});
//leads reports
Breadcrumbs::for('admin.reports.leads', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.leads.leads'));
});

Breadcrumbs::for('admin.reports.debts', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.reports.debts'));
});

//expenses reports
Breadcrumbs::for('admin.reports.expenses', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.reports.expenses'));
});

//products reports
Breadcrumbs::for('admin.reports.products', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.reports.reports'), route('admin.reports.index'));
    $trail->push(__('translation.products'));
});

//settings
Breadcrumbs::for('admin.settings.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'));
});

Breadcrumbs::for('admin.settings.general-settings-index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.general-settings'));
});

Breadcrumbs::for('admin.settings.payment-types', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.payment-type-settings'));
});

Breadcrumbs::for('admin.settings.payment-types.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.payment-type-settings'));
});

Breadcrumbs::for('admin.settings.payment-types.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.payment-type-settings'));
});

Breadcrumbs::for('admin.settings.payment-types.reorder', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.payment-type-settings'), route('admin.settings.payment-types'));
    $trail->push(__('translation.reorder'));
});

Breadcrumbs::for('admin.rooms.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.rooms.rooms'));
});

Breadcrumbs::for('admin.rooms.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.rooms.rooms'));
});

Breadcrumbs::for('admin.rooms.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.rooms.rooms'));
});

Breadcrumbs::for('admin.settings.sms', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.sms.settings'));
});

Breadcrumbs::for('admin.settings.sms.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.settings'), route('admin.settings.sms'));
    $trail->push(__('translation.sms.show_template'));
});

Breadcrumbs::for('admin.settings.sms.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.settings'), route('admin.settings.sms'));
    $trail->push(__('translation.sms.create_template'));
});

Breadcrumbs::for('admin.settings.sms.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.settings'), route('admin.settings.sms'));
    $trail->push(__('translation.sms.edit'));
});

Breadcrumbs::for('admin.settings.group-settings', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.groups.settings'));
});

Breadcrumbs::for('admin.settings.menu-settings-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.menu_settings'));
});

Breadcrumbs::for('admin.settings.staff-settings-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.staff_settings'));
});

//accounts
Breadcrumbs::for('admin.accounts.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.accounts.accounts'));
});
Breadcrumbs::for('admin.accounts.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.accounts.accounts'), route('admin.accounts.index'));
    $trail->push(__('translation.accounts.show'));
});

Breadcrumbs::for('admin.accounts.transfer', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.accounts.accounts'), route('admin.accounts.index'));
    $trail->push(__('translation.transfers.add'));
});

Breadcrumbs::for('admin.accounts.transaction-view', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.accounts.accounts'), route('admin.accounts.index'));
    $trail->push(__('translation.accounts.add_transaction'));
});

//branches
Breadcrumbs::for('admin.branches.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.branches.branches'));
});

Breadcrumbs::for('admin.branches.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.branches.branches'), route('admin.branches.index'));
    $trail->push(__('translation.branches.add'));
});

Breadcrumbs::for('admin.branches.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.branches.branches'), route('admin.branches.index'));
    $trail->push(__('translation.branches.show'));
});

Breadcrumbs::for('admin.branches.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.branches.branches'), route('admin.branches.index'));
    $trail->push(__('translation.branches.edit'));
});

//sms
Breadcrumbs::for('admin.sms.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.sms'));
});

Breadcrumbs::for('admin.sms.scheduled', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.scheduled'));
});

Breadcrumbs::for('admin.sms.batches', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.sms'), route('admin.sms.index'));
    $trail->push('SMS batchlar');
});

Breadcrumbs::for('admin.sms.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.sms'), route('admin.sms.index'));
    $trail->push(__('translation.sms.show'));
});

Breadcrumbs::for('admin.sms.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.sms.sms'), route('admin.sms.index'));
    $trail->push(__('translation.sms.create'));
});

//Exams
Breadcrumbs::for('admin.exams.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.exams.exams'));
});

Breadcrumbs::for('admin.exams.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.exams.exams'), route('admin.exams.index'));
    $trail->push(__('translation.exams.add'));
});

Breadcrumbs::for('admin.exams.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.exams.exams'), route('admin.exams.index'));
    $trail->push(__('translation.exams.show'));
});

Breadcrumbs::for('admin.exams.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.exams.exams'), route('admin.exams.index'));
    $trail->push(__('translation.exams.edit'));
});

Breadcrumbs::for('admin.exams.create-student-exams', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.exams.exams'), route('admin.exams.index'));
    $trail->push(__('translation.exams.create_student_exams'));
});

//Birthday
Breadcrumbs::for('admin.birthdays', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.dashboard'), route('admin.dashboard'));
    $trail->push(__('translation.birthdays'));
});

//Quizzes
Breadcrumbs::for('admin.quizzes.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.quizzes.quizzes'));
});

Breadcrumbs::for('admin.quizzes.create', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.quizzes.quizzes'), route('admin.quizzes.index'));
    $trail->push(__('translation.quizzes.add'));
});

Breadcrumbs::for('admin.quizzes.show', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.quizzes.quizzes'), route('admin.quizzes.index'));
    $trail->push(__('translation.quizzes.show'));
});

Breadcrumbs::for('admin.quizzes.edit', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.quizzes.quizzes'), route('admin.quizzes.index'));
    $trail->push(__('translation.quizzes.edit'));
});

//Tests
Breadcrumbs::for('admin.quizzes.create-test', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.quizzes.quizzes'), route('admin.quizzes.index'));
    $trail->push(__('translation.tests.add'));
});

Breadcrumbs::for('admin.settings.sms.default-templates', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.settings'), route('admin.settings.index'));
    $trail->push(__('translation.sms.settings'), route('admin.settings.sms'));
    $trail->push(__('translation.default_sms_templates'));
});

// Settings > Receipt Settings
Breadcrumbs::for('admin.settings.receipts.index', function (BreadcrumbTrail $trail) {
    $trail->parent('admin.settings.index');
    $trail->push(__('translation.receipts.settings'), route('admin.settings.receipts.index'));
});

//products
Breadcrumbs::for('admin.products.index', function (BreadcrumbTrail $trail) {
    $trail->push(__('translation.products'));
});
