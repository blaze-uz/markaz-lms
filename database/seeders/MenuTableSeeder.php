<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the 'menus' table exists before truncating
        if (! Schema::hasTable('menus')) {
            return;
        }
        // Truncate all menus
        Menu::truncate();

        // Check if the 'user_menu' table exists before truncating
        if (Schema::hasTable('user_menu')) {
            DB::table('user_menu')->truncate();
        }

        $menu_items = [
            [
                'name' => [
                    'uz' => 'Dashboard',
                    'ru' => 'Панель управления',
                ],
                'route' => 'admin.dashboard',
                'menu_active_route' => 'admin.dashboard',
                'icon' => 'mdi mdi-speedometer',
                'order' => 1,
                'permissions' => [],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'Guruhlar',
                    'ru' => 'Группы',
                ],
                'route' => 'admin.groups.index',
                'menu_active_route' => 'admin.groups.*',
                'icon' => 'mdi mdi-layers-triple-outline',
                'order' => 2,
                'permissions' => [],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => "O'quvchilar",
                    'ru' => 'Студенты',
                ],
                'route' => 'admin.students.index',
                'menu_active_route' => 'admin.students.*',
                'icon' => 'mdi mdi-account-box-multiple-outline',
                'order' => 3,
                'permissions' => ['student-index'],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'To\'lovlar',
                    'ru' => 'Оплаты',
                ],
                'route' => 'admin.payments.index',
                'menu_active_route' => 'admin.payments.*',
                'icon' => 'mdi mdi-cash-fast',
                'order' => 4,
                'permissions' => ['payment-index'],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'Hisobotlar',
                    'ru' => 'Отчеты',
                ],
                'route' => null,
                'menu_active_route' => 'admin.reports*',
                'icon' => 'mdi mdi-file-chart-outline',
                'order' => 5,
                'permissions' => ['report-index', 'report-educational', 'report-finance', 'report-leads', 'report-sms'],
                'children' => [
                    [
                        'name' => [
                            'uz' => 'Moliyaviy',
                            'ru' => 'Финансовый',
                        ],
                        'route' => 'admin.reports.index',
                        'menu_active_route' => 'admin.reports.index',
                        'icon' => 'mdi mdi-swap-horizontal',
                        'order' => 1,
                        'permissions' => ['report-index'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'O\'quv bo\'limi',
                            'ru' => 'Учебный отдел',
                        ],
                        'route' => 'admin.reports.educational',
                        'menu_active_route' => 'admin.reports.educational',
                        'icon' => 'mdi mdi-book-education-outline',
                        'order' => 2,
                        'permissions' => ['report-educational'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Lidlar',
                            'ru' => 'Лиды',
                        ],
                        'route' => 'admin.reports.leads',
                        'menu_active_route' => 'admin.reports.leads',
                        'icon' => 'mdi mdi-account-multiple-check-outline',
                        'order' => 3,
                        'permissions' => ['report-leads'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Qarzdorlar',
                            'ru' => 'Должники',
                        ],
                        'route' => 'admin.reports.debts',
                        'menu_active_route' => 'admin.reports.debts',
                        'icon' => 'mdi mdi-cash-minus',
                        'order' => 4,
                        'permissions' => [],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'SMS',
                            'ru' => 'SMS',
                        ],
                        'route' => 'admin.reports.sms',
                        'menu_active_route' => 'admin.reports.sms',
                        'icon' => 'mdi mdi-message-text-outline',
                        'order' => 5,
                        'permissions' => ['report-sms'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Davomat',
                            'ru' => 'Посещаемость',
                        ],
                        'route' => 'admin.reports.attendance',
                        'menu_active_route' => 'admin.reports.attendance',
                        'icon' => 'mdi mdi-calendar-check-outline',
                        'order' => 6,
                        'permissions' => [],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Xarajatlar',
                            'ru' => 'Расходы',
                        ],
                        'route' => 'admin.reports.expenses',
                        'menu_active_route' => 'admin.reports.expenses',
                        'icon' => 'mdi mdi-cash-remove',
                        'order' => 6,
                        'permissions' => [],
                        'children' => [],
                    ],
                ],
            ],
            [
                'name' => [
                    'uz' => 'Xarajatlar',
                    'ru' => 'Расходы',
                ],
                'route' => null,
                'menu_active_route' => ['admin.expenses.*', 'admin.users.salaries*'],
                'icon' => 'mdi mdi-cash-remove',
                'order' => 6,
                'permissions' => ['expense-index', 'salary-index'],
                'children' => [
                    [
                        'name' => [
                            'uz' => 'Xarajatlar',
                            'ru' => 'Расходы',
                        ],
                        'route' => 'admin.expenses.index',
                        'menu_active_route' => 'admin.expenses.*',
                        'icon' => 'mdi mdi-transfer',
                        'order' => 1,
                        'permissions' => ['expense-index'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Moashlar',
                            'ru' => 'Зарплаты',
                        ],
                        'route' => 'admin.users.salaries',
                        'menu_active_route' => 'admin.users.salaries',
                        'icon' => 'mdi mdi-cash-multiple',
                        'order' => 2,
                        'permissions' => ['salary-index'],
                        'children' => [],
                    ],
                ],
            ],
            [
                'name' => [
                    'uz' => 'Lidlar',
                    'ru' => 'Лиды',
                ],
                'route' => 'admin.leads.inertia',
                'menu_active_route' => 'admin.leads.*',
                'icon' => 'mdi mdi-account-filter-outline',
                'order' => 7,
                'permissions' => ['lead-index'],
                'children' => [],
            ],
            // [
            //     'name' => [
            //         'uz' => 'Lidlar',
            //         'ru' => 'Лиды',
            //     ],
            //     'route' => null,
            //     'menu_active_route' => 'admin.leads*',
            //     'icon' => 'mdi mdi-account-filter-outline',
            //     'order' => 7,
            //     'permissions' => ['lead-index'],
            //     'children' => [
            //         [
            //             'name' => [
            //                 'uz' => 'Lid qo\'shish',
            //                 'ru' => 'Добавить лид',
            //             ],
            //             'route' => 'admin.leads.create',
            //             'menu_active_route' => 'admin.leads.create',
            //             'icon' => 'mdi mdi-account-plus-outline',
            //             'order' => 1,
            //             'permissions' => [],
            //             'children' => [],
            //         ],
            //         [
            //             'name' => [
            //                 'uz' => 'Lidlar',
            //                 'ru' => 'Лиды',
            //             ],
            //             'route' => 'admin.leads.index',
            //             'menu_active_route' => 'admin.leads.index',
            //             'icon' => 'mdi mdi-account-filter',
            //             'order' => 2,
            //             'permissions' => [],
            //             'children' => [],
            //         ],
            //         [
            //             'name' => [
            //                 'uz' => 'Eslatmalar',
            //                 'ru' => 'Напоминания',
            //             ],
            //             'route' => 'admin.leads.reminders',
            //             'menu_active_route' => 'admin.leads.reminders',
            //             'icon' => 'mdi mdi-bell-alert-outline',
            //             'order' => 3,
            //             'permissions' => [],
            //             'children' => [],
            //         ],
            //         [
            //             'name' => [
            //                 'uz' => 'Qo\'ng\'iroqlar',
            //                 'ru' => 'Звонки',
            //             ],
            //             'route' => 'admin.leads.calls-index',
            //             'menu_active_route' => 'admin.leads.calls-index',
            //             'icon' => 'mdi mdi-phone-outline',
            //             'order' => 4,
            //             'permissions' => [],
            //             'children' => [],
            //         ],
            //     ],
            // ],
            [
                'name' => [
                    'uz' => 'Xodimlar',
                    'ru' => 'Сотрудники',
                ],
                'route' => null,
                'menu_active_route' => ['admin.users.*', 'admin.roles.*', 'admin.departments.*'],
                'icon' => 'mdi mdi mdi-account-group-outline',
                'order' => 8,
                'permissions' => ['user-index'],
                'children' => [
                    [
                        'name' => [
                            'uz' => 'Foydalanuvchilar',
                            'ru' => 'Сотрудники',
                        ],
                        'route' => 'admin.users.index',
                        'menu_active_route' => 'admin.users.*',
                        'icon' => 'mdi mdi-account-multiple',
                        'order' => 1,
                        'permissions' => ['user-index'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Bo\'limlar',
                            'ru' => 'Отделы',
                        ],
                        'route' => 'admin.departments.index',
                        'menu_active_route' => 'admin.departments.*',
                        'icon' => 'mdi mdi-office-building-outline',
                        'order' => 2,
                        'permissions' => ['department-index'],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Lavozimlar',
                            'ru' => 'Позиции',
                        ],
                        'route' => 'admin.roles.index',
                        'menu_active_route' => 'admin.roles.*',
                        'icon' => 'mdi mdi-security',
                        'order' => 3,
                        'permissions' => [],
                        'children' => [],
                    ],
                ],
            ],
            [
                'name' => [
                    'uz' => 'Hisoblar',
                    'ru' => 'Счета',
                ],
                'route' => 'admin.accounts.index',
                'menu_active_route' => 'admin.accounts.*',
                'icon' => 'mdi mdi-database-cog',
                'order' => 9,
                'permissions' => ['account-index'],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'Kurslar',
                    'ru' => 'Курсы',
                ],
                'route' => 'admin.courses.index',
                'menu_active_route' => 'admin.courses.*',
                'icon' => 'mdi mdi-folder-multiple-outline',
                'order' => 10,
                'permissions' => ['course-index'],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'SMS',
                    'ru' => 'СМС',
                ],
                'route' => null,
                'menu_active_route' => 'admin.sms.*',
                'icon' => 'mdi mdi-message-text-outline',
                'order' => 11,
                'permissions' => [
                    'sms-index',
                ],
                'children' => [
                    [
                        'name' => [
                            'uz' => 'SMS',
                            'ru' => 'СМС',
                        ],
                        'route' => 'admin.sms.index',
                        'menu_active_route' => ['admin.sms.index', 'admin.sms.show'],
                        'icon' => 'mdi mdi-clipboard-list-outline',
                        'order' => 1,
                        'permissions' => [
                            'sms-index',
                        ],
                        'children' => [],
                    ],
                    [
                        'name' => [
                            'uz' => 'Rejalashtirilgan',
                            'ru' => 'Запланированные СМС',
                        ],
                        'route' => 'admin.sms.scheduled',
                        'menu_active_route' => 'admin.sms.scheduled',
                        'icon' => 'mdi mdi-calendar-clock',
                        'order' => 2,
                        'permissions' => [
                            'sms-index',
                        ],
                        'children' => [],
                    ],
                ],
            ],
            [
                'name' => [
                    'uz' => 'Telegram',
                    'ru' => 'Telegram',
                ],
                'route' => 'admin.telegram.messages.index',
                'menu_active_route' => 'admin.telegram.*',
                'icon' => 'ri-telegram-line',
                'order' => 12,
                'permissions' => [
                    'sms-create',
                ],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'Imtihonlar',
                    'ru' => 'Экзамены',
                ],
                'route' => 'admin.exams.index',
                'menu_active_route' => 'admin.exams.*',
                'icon' => 'mdi mdi-file-document-edit-outline',
                'order' => 12,
                'permissions' => [],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'Mahsulotlar',
                    'ru' => 'Продукты',
                ],
                'route' => 'admin.products.index',
                'menu_active_route' => 'admin.products.*',
                'icon' => 'mdi mdi-package-variant-closed',
                'order' => 12,
                'permissions' => ['product-index'],
                'children' => [],
            ],
            [
                'name' => [
                    'uz' => 'Sozlamalar',
                    'ru' => 'Настройки',
                ],
                'route' => 'admin.settings.index',
                'menu_active_route' => 'admin.settings.*',
                'icon' => 'mdi mdi-cog-outline',
                'order' => 13,
                'permissions' => ['settings-index'],
                'children' => [],
            ],
        ];

        foreach ($menu_items as $item) {
            $menu_active_route_array = is_array($item['menu_active_route']) ? $item['menu_active_route'] : [$item['menu_active_route']];
            $menu = Menu::create([
                'name' => $item['name'],
                'route' => $item['route'],
                'menu_active_route' => $menu_active_route_array,
                'icon' => $item['icon'],
                'order' => $item['order'],
                'permissions' => $item['permissions'],
            ]);

            foreach ($item['children'] as $child) {
                $child_menu_active_route_array = is_array($child['menu_active_route']) ? $child['menu_active_route'] : [$child['menu_active_route']];
                $menu->children()->create([
                    'name' => $child['name'],
                    'route' => $child['route'],
                    'menu_active_route' => $child_menu_active_route_array,
                    'icon' => $child['icon'],
                    'order' => $child['order'],
                    'permissions' => $child['permissions'],
                    'parent_id' => $menu->id,
                ]);
            }
        }
    }
}
