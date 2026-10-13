<?php

namespace App\Enums;

enum AccountEnum: int
{
    // 1000 - asosiy hisob
    // 2001 - o'qituvchi va hodimlar
    // 3001 - guruhlar
    // 4001 - o'quvchilar
    case MAIN = 1000;
    case STAFF = 2001;
    case GROUP = 3001;
    case STUDENT = 4001;
}
