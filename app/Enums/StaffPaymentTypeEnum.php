<?php

namespace App\Enums;

enum StaffPaymentTypeEnum: string
{
    case SALARY = 'salary';
    case GROUP = 'group';
    case WITHDRAW = 'withdraw';

}
